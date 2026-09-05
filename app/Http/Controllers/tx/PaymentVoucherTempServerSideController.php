<?php

namespace App\Http\Controllers\tx;

use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use App\Models\Auto_inc;
use App\Models\Mst_coa;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use App\Models\Mst_supplier;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use App\Models\Tx_hutang_tmp;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_lokal_journal_detail;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_receipt_order;
use App\Models\Tx_tagihan_supplier;
use App\Models\Tx_tagihan_supplier_detail;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_automatic_journal_detail_ext;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentVoucherTempServerSideController extends Controller
{
    protected $title  = 'Pembayaran Supplier (Temp)';
    protected $folder = 'payment-voucher-temp';

    public function create(Request $request)
    {
        // gate email: hanya andydch & maeger (pertahanan ganda selain menu)
        abort_unless(Auth::check() && in_array(Auth::user()->email, ['andydch@koidigital.co.id','maeger@koidigital.co.id'], true), 403, 'Akses ditolak.');
        // combo terpilih (persist antar-reload via query string)
        $fSupplier = $request->query('supplier', '');
        $fNpwp     = in_array($request->query('npwp'), ['P','N']) ? $request->query('npwp') : '';
        $fTagihan  = (int)$request->query('tagihan', 0);

        $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)->first();
        $qCurrency = Mst_global::where(['id'=>3,'data_cat'=>'currency','active'=>'Y'])->first();
        $qVat      = Mst_global::where(['data_cat'=>'vat','active'=>'Y'])->first();

        // 1. Supplier: distinct kode_supplier dari tx_hutang_tmp (nama via lookup PHP, hindari bentrok collation)
        $codes = Tx_hutang_tmp::distinct()->orderBy('kode_supplier')->pluck('kode_supplier')->all();
        $suppliers = Mst_supplier::whereIn('supplier_code', $codes)->orderBy('name','ASC')->get()
            ->keyBy('supplier_code');
        $supplierList = [];
        foreach ($codes as $c) {
            $supplierList[] = [
                'kode' => $c,
                'nama' => optional($suppliers->get($c))->name ?? $c,
            ];
        }

        // 2. ID PV yang sudah memakai tagihan (filter 5b) — dibaca sekali
        $pvTakenIds = Tx_payment_voucher::whereNotNull('tagihan_supplier_id')
            ->where('active','Y')->pluck('tagihan_supplier_id')->all();

        // 3. Opsi No Tagihan: cts_id, filter supplier+npwp+belum dibayar+jurnal_date < 2026
        $tagihanQuery = Tx_hutang_tmp::whereYear('jurnal_date', '<', 2026)
            ->when($fSupplier, fn($q) => $q->where('kode_supplier', $fSupplier))
            ->when($fNpwp,     fn($q) => $q->where('journal_type', $fNpwp))
            ->whereNotIn('cts_id', $pvTakenIds ?: [0])
            ->orderBy('cts_id')->get();

        // 4. Baris definitif = yang terpilih di dropdown No Tagihan
        $hutang = null;
        $tagihans = [];
        foreach ($tagihanQuery as $t) {
            $ts = Tx_tagihan_supplier::where('id', $t->cts_id)->first();
            $tagihans[] = ['id'=>$t->cts_id, 'no'=>optional($ts)->tagihan_supplier_no ?? ('#'.$t->cts_id)];
            if ($fTagihan && $t->cts_id == $fTagihan) { $hutang = $t; }
        }

        // 5. Data pelengkap label & tabel RO (hanya saat kombinasi lengkap)
        $bayarVia = null; $coa = null; $roList = collect(); $sumRo = 0;
        if ($hutang) {
            $bayarVia = Mst_global::where('id', $hutang->bayar_via_id)->first();
            $coa = Mst_coa::where('id', $hutang->coa_id)->first();
            $roList = DB::table('tx_tagihan_supplier_details as tsd')
                ->join('tx_receipt_orders as ro', 'ro.id', '=', 'tsd.receipt_order_id')
                ->where('tsd.tagihan_supplier_id', $hutang->cts_id)
                ->where('tsd.active', 'Y')
                ->select('ro.id as ro_id','ro.invoice_no','ro.receipt_no','ro.receipt_date',
                         'ro.remark as description','tsd.total_price_per_ro')
                ->orderBy('ro.invoice_no')->get();
            $sumRo = $roList->sum('total_price_per_ro');
        }

        $data = [
            'title' => $this->title, 'folder' => $this->folder,
            'userLogin' => $userLogin, 'qCurrency' => $qCurrency, 'qVat' => $qVat,
            'supplierList' => $supplierList, 'fSupplier' => $fSupplier,
            'npwpOptions' => ['P','N'], 'fNpwp' => $fNpwp,
            'tagihans' => $tagihans, 'fTagihan' => $fTagihan,
            'hutang' => $hutang, 'bayarVia' => $bayarVia, 'coa' => $coa,
            'roList' => $roList, 'sumRo' => $sumRo,
            'payment_mode_string' => explode('|', (string)env('METHOD_BAYAR_SUPPLIER_NAME')),
            'payment_mode_id'     => explode('|', (string)env('METHOD_BAYAR_SUPPLIER_ID')),
        ];
        return view('tx.'.$this->folder.'.create', $data);
    }

    public function store(Request $request)
    {
        // gate email: hanya andydch & maeger (pertahanan ganda selain menu)
        abort_unless(Auth::check() && in_array(Auth::user()->email, ['andydch@koidigital.co.id','maeger@koidigital.co.id'], true), 403, 'Akses ditolak.');
        // privilege mengikuti menu Pembayaran Supplier
        $qCheckPriv = Mst_menu_user::where(['menu_id'=>50,'user_id'=>Auth::user()->id,'user_access_read'=>'Y'])->first();
        if (!$qCheckPriv && Auth::user()->id != 1 && !in_array(Auth::user()->email, ['andydch@koidigital.co.id','maeger@koidigital.co.id'], true)) {
            return redirect()->back()->withInput()
                ->with('status-error', env('ERR_MSG_02') ?: 'You are not allowed to access this page!');
        }

        $request->validate([
            'hutang_tmp_id' => 'required|numeric',
        ], ['hutang_tmp_id.required' => 'Lengkapi pilihan Supplier, NPWP, dan No Tagihan Supplier']);

        $tmp = Tx_hutang_tmp::find($request->hutang_tmp_id);
        if (!$tmp) { return redirect()->back()->with('status-error','Data hutang tmp tidak ditemukan.'); }
        if ((int)date('Y', strtotime($tmp->jurnal_date)) >= 2026) {
            return redirect()->back()->with('status-error','Jurnal date harus sebelum tahun 2026.');
        }
        $sudahDibayar = Tx_payment_voucher::where('tagihan_supplier_id', $tmp->cts_id)->where('active','Y')->first();
        if ($sudahDibayar) {
            return redirect()->back()->with('status-error','Tagihan sudah dibayar via '.$sudahDibayar->payment_voucher_no.'.');
        }
        $qCoa = Mst_coa::where('id', $tmp->coa_id)->where('is_cashflow','Y')->where('active','Y')->first();
        if (!$qCoa) { return redirect()->back()->with('status-error','COA rekening bukan cashflow / tidak aktif.'); }

        DB::beginTransaction();
        try {
            // ---- generate nomor PV race-safe ----
            // PV number by Journal Date year — 06/02/2024 => PVM24-xxxx (MAX-scan + lock)
            $maxAttempts = 3;
            $attempt = 0;
            $payment_voucher_no = null;
            $yy = date_format(date_create($tmp->jurnal_date), "y");
            $prefix = env('P_PAYMENT_VOUCHER').$yy.'-';
            while ($attempt < $maxAttempts) {
                try {
                    $identityName = 'tx_payment_vouchers';
                    $autoInc = Auto_inc::where(['identity_name'=>$identityName])->lockForUpdate()->first();
                    $last = Tx_payment_voucher::where('payment_voucher_no','LIKE',$prefix.'%')->where('payment_voucher_no','NOT LIKE','%Draft%')->where('active','Y')->selectRaw('CAST(REPLACE(payment_voucher_no,?, \'\') AS UNSIGNED) AS lastNum', [$prefix])->orderBy('lastNum','DESC')->lockForUpdate()->first();
                    $newInc = $last ? ((int)$last->lastNum + 1) : 1;
                    if ($autoInc) {
                        Auto_inc::where(['identity_name'=>$identityName])->update(['id_auto_inc'=>$newInc, 'updated_at'=>now()]);
                    } else {
                        Auto_inc::create(['identity_name'=>$identityName,'id_auto_inc'=>$newInc]);
                    }
                    $zero = str_repeat('0', max(0, 5 - strlen(strval($newInc))));
                    $payment_voucher_no = $prefix.$zero.strval($newInc);
                    if (Tx_payment_voucher::where('payment_voucher_no', $payment_voucher_no)->lockForUpdate()->exists()) {
                        throw new \Illuminate\Database\QueryException('mysql', 'Duplicate PV no guard', []);
                    }
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) throw $e;
                    usleep(100000 * $attempt);
                }
            }
            if (!$payment_voucher_no) throw new \Exception('Gagal generate nomor PV setelah retry.');

            $supplier = Mst_supplier::where('supplier_code',$tmp->kode_supplier)->where('active','Y')->first();
            if (!$supplier) throw new \Exception('Supplier '.$tmp->kode_supplier.' tidak ada di master.');
            $roDetails = Tx_tagihan_supplier_detail::where('tagihan_supplier_id',$tmp->cts_id)
                ->where('active','Y')->get();
            if ($roDetails->count()==0) throw new \Exception('Tagihan tidak punya detail RO.');

            $qVat = Mst_global::where(['data_cat'=>'vat','active'=>'Y'])->first();
            $vatPct = ($tmp->journal_type=='P') ? (float)($qVat->numeric_val ?? 0) : 0;
            $sumDetail = (float)$roDetails->sum('total_price_per_ro');
            $paymentTotal = ($tmp->journal_type=='P')
                ? round((float)$tmp->total / (1 + $vatPct/100), 2)
                : (float)$tmp->total;
            $factor = ($sumDetail>0) ? ($paymentTotal/$sumDetail) : 1;

            $ins = Tx_payment_voucher::create([
                'payment_voucher_no'    => $payment_voucher_no,
                'supplier_id'           => $supplier->id,
                'payment_type_id'       => $tmp->journal_type,
                'journal_type_id'       => $tmp->journal_type,
                'payment_date'          => $tmp->jurnal_date,
                'payment_total'         => $paymentTotal,
                'payment_total_after_vat' => round($paymentTotal * (1 + $vatPct/100), 2),
                'payment_mode'          => $tmp->metode_bayar_id,
                'coa_id'                => $tmp->coa_id,
                'tagihan_supplier_id'   => $tmp->cts_id,
                'reference_no'          => ($tmp->no_giro ?: null),
                'reference_date'        => $tmp->jurnal_date,
                'remark'                => 'Pembayaran Supplier (Temp)',
                'admin_bank'            => (float)$tmp->admin_bank,
                'biaya_asuransi'        => (float)$tmp->biaya_asuransi,
                'biaya_kirim'           => (float)$tmp->biaya_kirim,
                'biaya_lainnya'         => (float)$tmp->biaya_lain,
                'diskon_pembelian'      => (float)$tmp->discount,
                'vat_num'               => $vatPct,
                'is_full_payment'       => 'Y',
                'is_draft'              => 'N',
                'active'                => 'Y',
                'created_by'            => Auth::user()->id,
                'updated_by'            => Auth::user()->id,
            ]);
            $maxId = $ins->id;

            foreach ($roDetails as $rd) {
                $ro = Tx_receipt_order::find($rd->receipt_order_id);
                if (!$ro) continue;
                $tp = round((float)$rd->total_price_per_ro * $factor, 2);
                Tx_payment_voucher_invoice::create([
                    'payment_voucher_id'   => $maxId,
                    'receipt_order_id'     => $rd->receipt_order_id,
                    'invoice_no'           => $ro->invoice_no,
                    'description'          => 'Pembayaran Supplier (Temp)',
                    'total_payment'        => $tp,
                    'total_payment_after_vat' => round($tp * (1 + $vatPct/100), 2),
                    'total_payment_before_retur' => $tp,
                    'total_payment_before_retur_after_vat' => round($tp * (1 + $vatPct/100), 2),
                    'is_full_payment'      => 'Y',
                    'active'               => 'Y',
                    'created_by'           => Auth::user()->id,
                    'updated_by'           => Auth::user()->id,
                ]);
            }

            // ---- auto-approve user 5 ----
            $APPROVER_ID = 5;
            Tx_payment_voucher::where('id', $maxId)->update([
                'approved_by' => $APPROVER_ID,
                'approved_at' => now(),
                'updated_by'  => Auth::user()->id,
            ]);
            // refresh untuk jurnal
            $ins->refresh();
            $q = $ins;

            // ---- Automatic Journal (copy dari PaymentVoucherApprovalServerSideController) ----
            // akan diisi Task 5: panggil helper journal dengan lockForUpdate + retry
            // Untuk sekarang, buat placeholder yang akan diganti full Task 5.
            // Jika belum, jurnal tidak terbentuk tapi PV tetap tersimpan (warning flash)
            $result = $this->createJournalForVoucher($q, $payment_voucher_no, $APPROVER_ID, $tmp);
            if (!$result['ok']) {
                throw new \Exception('Jurnal tidak bisa dibentuk: '.$result['reason']);
            }

            DB::commit();
            session()->flash('status', 'Pembayaran Supplier (Temp) tersimpan dan ter-approve.');
            return redirect(env('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
        } catch (\Exception $e) {
            DB::rollBack();
            if (str_contains($e->getMessage(), 'Jurnal tidak bisa dibentuk')) {
                return redirect()->back()->withInput()->with('status-error', $e->getMessage());
            }
            if (str_contains($e->getMessage(), 'Duplicate')) {
                return redirect()->back()->withInput()->with('status-error', 'Nomor PV/Jurnal bentrok (race). Silakan coba Save lagi.');
            }
            return redirect()->back()->withInput()->with('status-error', env('ERR_MSG_01') ?: $e->getMessage());
        }
    }

    private function createJournalForVoucher($q, $payment_voucher_no, $APPROVER_ID, $tmp)
    {
        // Task 5: full journal race-safe (lockForUpdate + retry + lastCounterIfAny) - copy-adapt Approval 361-1078
        try {
            $isPpn = ($q->payment_type_id == 'P' || $q->journal_type_id == 'P');
            $branch_id = $tmp->branch_id;
            if (!$branch_id) {
                $firstInv = Tx_payment_voucher_invoice::where('payment_voucher_id', $q->id)->first();
                if ($firstInv) {
                    $ro = Tx_receipt_order::find($firstInv->receipt_order_id);
                    if ($ro) $branch_id = $ro->branch_id ?? $tmp->branch_id;
                }
            }
            $payment_mode = $q->payment_mode;
            $methodMap = ['2'=>'Bank','3'=>'Advance Payment'];
            $methodNm = $methodMap[(string)$payment_mode] ?? 'Cash';
            $autoJournalId = $isPpn ? 8 : 13;
            $qAut = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>$autoJournalId,
                'branch_id'=>$branch_id,
                'method_id'=>$payment_mode,
                'active'=>'Y',
            ])->first();
            if (!$qAut) return ['ok'=>false,'reason'=>'Setup Automatic Journal id '.$autoJournalId.' tidak ada/tidak aktif (branch '.$branch_id.' / metode '.$methodNm.')'];

            // description untuk jurnal
            $description = 'Pembayaran Supplier (Temp) '.$payment_voucher_no;
            $total_cash = (float)$q->payment_total_after_vat + (float)$q->admin_bank + (float)$q->biaya_asuransi + (float)$q->biaya_kirim + (float)$q->biaya_lainnya - (float)$q->diskon_pembelian;

            // retry loop untuk nomor jurnal
            $maxAttempts = 3; $attempt = 0; $journal_no = null; $YearMonth = null;
            while ($attempt < $maxAttempts) {
                try {
                    $yearTemp = date_format(date_create($q->payment_date), "y");
                    $monthTemp = date_format(date_create($q->payment_date), "m");
                    $ymTemp = $yearTemp.$monthTemp;
                    $isGeneral = $isPpn;
                    $identityName = $isGeneral ? 'tx_general_journal' : 'tx_lokal_journal';
                    $prefix = $isGeneral ? env('P_GENERAL_JURNAL') : env('P_LOKAL_JURNAL');
                    $autoInc = Auto_inc::where(['identity_name'=>$identityName])->lockForUpdate()->first();
                    $newInc = 1;
                    $YearMonth = $yearTemp.$monthTemp;
                    if ($autoInc) {
                        $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                        $dateNow = date("ym");
                        if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                            $lastCounterIfAny = $isGeneral
                                ? Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first()
                                : Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first();
                            if ($lastCounterIfAny) $newInc = $lastCounterIfAny->lastCounter+1;
                        } else {
                            $newInc = (int)($autoInc->id_auto_inc ?: 0) + 1;
                            Auto_inc::where(['identity_name'=>$identityName])->update(['id_auto_inc'=>$newInc]);
                        }
                    } else {
                        $lastCounterIfAny = $isGeneral
                            ? Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first()
                            : Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first();
                        if ($lastCounterIfAny) $newInc = $lastCounterIfAny->lastCounter+1;
                        Auto_inc::create(['identity_name'=>$identityName,'id_auto_inc'=>$newInc]);
                        $YearMonth = date('y').date('m');
                    }
                    $zero = str_repeat('0', max(0, 5 - strlen(strval($newInc))));
                    $journal_no = $prefix.$YearMonth.$zero.strval($newInc);
                    $exists = $isGeneral
                        ? Tx_general_journal::where('general_journal_no',$journal_no)->lockForUpdate()->exists()
                        : Tx_lokal_journal::where('general_journal_no',$journal_no)->lockForUpdate()->exists();
                    if ($exists) throw new \Illuminate\Database\QueryException('mysql','Duplicate journal no',[]);
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) throw $e;
                    usleep(100000 * $attempt);
                }
            }
            if (!$journal_no) return false;

            // buat header jurnal
            if ($isPpn) {
                $insJournal = Tx_general_journal::create([
                    'general_journal_no'=>$journal_no,
                    'general_journal_date'=>$q->payment_date,
                    'total_debit'=>$total_cash,
                    'total_kredit'=>$total_cash,
                    'module_no'=>$payment_voucher_no,
                    'automatic_journal_id'=>8,
                    'active'=>'Y',
                    'created_by'=>$APPROVER_ID,
                    'updated_by'=>$APPROVER_ID,
                ]);
                $journalId = $insJournal->id;
                // detail: ambil dari mst_automatic_journal_details jika ada, else minimal
                // untuk Temp, buat minimal balancing: debit hutang, kredit cash/bank
                // coba lookup coa hutang & cash dari automatic setup
                $qDetailHutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>8,
                    'branch_id'=>$branch_id,
                    'method_id'=>$payment_mode,
                    'active'=>'Y',
                ])->whereRaw('LOWER(`desc`)=\'hutang\'')->first();
                // cash/bank/advance — union ext (Approval 509-528)
                $qAutJournal_cash_ext = Mst_automatic_journal_detail_ext::select('coa_code_id')
                    ->where(['auto_journal_id'=>8,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])
                    ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'');
                $qDetailCash = Mst_automatic_journal_detail::select('coa_code_id')
                    ->where(['auto_journal_id'=>8,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])
                    ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                    ->union($qAutJournal_cash_ext)->first();
                // hutang debit
                Tx_general_journal_detail::create([
                    'general_journal_id'=>$journalId,
                    'coa_id'=> $qDetailHutang->coa_code_id ?? DB::table('mst_coas')->where('coa_code_complete','like','%HUTANG%')->value('id') ?? $q->coa_id,
                    'description'=>$description,
                    'debit'=>$q->payment_total_after_vat,
                    'kredit'=>0,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
                // biaya-biaya jika ada coa
                foreach (['admin_bank'=>'bank admin','biaya_asuransi'=>'biaya asuransi','biaya_kirim'=>'biaya kirim','biaya_lainnya'=>'biaya lainnya'] as $field=>$like){
                    $val = (float)$q->$field;
                    if($val>0){
                        $qDet = Mst_automatic_journal_detail::where(['auto_journal_id'=>8,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\''.strtolower($like).'\'')->first();
                        if($qDet && $qDet->coa_code_id>0){
                            Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qDet->coa_code_id,'description'=>$description,'debit'=>$val,'kredit'=>0,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                        }
                    }
                }
                if((float)$q->diskon_pembelian>0){
                    $qDisc = Mst_automatic_journal_detail::where(['auto_journal_id'=>8,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'discount\'')->first();
                    Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=> $qDisc->coa_code_id ?? $q->coa_id,'description'=>$description,'debit'=>0,'kredit'=>(float)$q->diskon_pembelian,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                }
                Tx_general_journal_detail::create([
                    'general_journal_id'=>$journalId,
                    'coa_id'=> $qDetailCash->coa_code_id ?? $q->coa_id,
                    'description'=>$description,
                    'debit'=>0,
                    'kredit'=>$total_cash,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
            } else {
                $insJournal = Tx_lokal_journal::create([
                    'general_journal_no'=>$journal_no,
                    'general_journal_date'=>$q->payment_date,
                    'total_debit'=>$total_cash,
                    'total_kredit'=>$total_cash,
                    'module_no'=>$payment_voucher_no,
                    'automatic_journal_id'=>13,
                    'active'=>'Y',
                    'created_by'=>$APPROVER_ID,
                    'updated_by'=>$APPROVER_ID,
                ]);
                $journalId = $insJournal->id;
                Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>$journalId,
                    'coa_id'=> Mst_automatic_journal_detail::where(['auto_journal_id'=>13,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->value('coa_code_id') ?? $q->coa_id,
                    'description'=>$description,
                    'debit'=>$q->payment_total_after_vat,
                    'kredit'=>0,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
                Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>$journalId,
                    'coa_id'=>$q->coa_id,
                    'description'=>$description,
                    'debit'=>0,
                    'kredit'=>$total_cash,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
            }
            return ['ok'=>true,'reason'=>''];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(),'Duplicate journal')) throw new \Illuminate\Database\QueryException('mysql','Duplicate journal no',[]);
            return ['ok'=>false,'reason'=>$e->getMessage()];
        }
    }
}
