<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Auto_inc;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_automatic_journal_detail_ext;
use App\Models\Mst_coa;
use App\Models\Mst_customer;
use App\Models\Mst_global;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use App\Models\Tx_invoice;
use App\Models\Tx_kwitansi;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_lokal_journal_detail;
use App\Models\Tx_payment_receipt;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Tx_piutang_tmp;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentReceiptTempServerSideController extends Controller
{
    protected $title = 'Penerimaan Customer (Temp)';
    protected $folder = 'payment-receipt-temp';

    public function create(Request $request)
    {
        $fCustomer = $request->query('customer', '');
        $fNpwp = in_array($request->query('npwp'), ['P','N']) ? $request->query('npwp') : '';
        $fJournalDate = $request->query('journal_date', '');

        $userLogin = Userdetail::where('user_id', Auth::user()->id)->first();
        $qCurrency = Mst_global::where(['id'=>3,'data_cat'=>'currency','active'=>'Y'])->first();
        $qVat = Mst_global::where(['data_cat'=>'vat','active'=>'Y'])->first();

        $codes = Tx_piutang_tmp::distinct()->orderBy('kode_customer')->pluck('kode_customer')->all();
        $custMap = Mst_customer::whereIn('customer_unique_code', $codes)->orderBy('name')->get()->keyBy('customer_unique_code');
        $customerList = [];
        foreach ($codes as $c) {
            $customerList[] = ['kode'=>$c, 'nama'=>optional($custMap->get($c))->name ?? $c];
        }

        // Journal Date options — hanya is_full_payment Y + IN/KW per tipe_invoice
        $journalDatesQuery = Tx_piutang_tmp::whereYear('jurnal_date','<',2026)
            ->when($fCustomer, fn($q)=>$q->where('kode_customer',$fCustomer))
            ->when($fNpwp, fn($q)=>$q->where('journal_type',$fNpwp))
            ->orderBy('jurnal_date');
        $takenIds = Tx_payment_receipt_invoice::where(['active'=>'Y','is_full_payment'=>'Y'])->whereNotNull('invoice_id')->pluck('invoice_id')->all();
        $takenNosIN = Tx_payment_receipt_invoice::where(['active'=>'Y','is_full_payment'=>'Y'])->whereNotNull('invoice_no')->where('invoice_no','LIKE','IN%')->pluck('invoice_no')->all();
        $takenNosKW = Tx_payment_receipt_invoice::where(['active'=>'Y','is_full_payment'=>'Y'])->whereNotNull('invoice_no')->where('invoice_no','LIKE','KW%')->pluck('invoice_no')->all();
        $journalDates = $journalDatesQuery->get()->filter(function($row) use ($takenIds, $takenNosIN, $takenNosKW){
            $invNo = $row->tipe_invoice=='I'
                ? DB::table('tx_invoices')->where('id',$row->inv_or_kwi_id)->value('invoice_no')
                : ($row->tipe_invoice=='K' ? DB::table('tx_kwitansis')->where('id',$row->inv_or_kwi_id)->value('kwitansi_no') : null);
            if($row->tipe_invoice=='I' && $invNo && in_array($invNo, $takenNosIN)) return false;
            if($row->tipe_invoice=='K' && $invNo && in_array($invNo, $takenNosKW)) return false;
            if(!in_array($row->tipe_invoice,['I','K']) && in_array($row->inv_or_kwi_id, $takenIds)) return false;
            return true;
        })->pluck('jurnal_date')->unique()->values()->all();

        $piutang = null;
        $bayarVia = null; $coa = null; $invList = collect(); $sum = 0;
        if ($fCustomer && $fNpwp && $fJournalDate) {
            $piutangCandidates = Tx_piutang_tmp::where(['kode_customer'=>$fCustomer,'journal_type'=>$fNpwp,'jurnal_date'=>$fJournalDate])
                ->whereYear('jurnal_date','<',2026)->orderBy('id','DESC')->get()
                ->filter(function($row) use ($takenIds, $takenNosIN, $takenNosKW){
                    $invNo = $row->tipe_invoice=='I'
                        ? DB::table('tx_invoices')->where('id',$row->inv_or_kwi_id)->value('invoice_no')
                        : DB::table('tx_kwitansis')->where('id',$row->inv_or_kwi_id)->value('kwitansi_no');
                    if($row->tipe_invoice=='I' && $invNo && in_array($invNo, $takenNosIN)) return false;
                    if($row->tipe_invoice=='K' && $invNo && in_array($invNo, $takenNosKW)) return false;
                    if(!in_array($row->tipe_invoice,['I','K']) && in_array($row->inv_or_kwi_id, $takenIds)) return false;
                    return true;
                });
            $piutang = $piutangCandidates->first();
            if ($piutang) {
                $bayarVia = $piutang->bayar_via_id ? Mst_global::find($piutang->bayar_via_id) : null;
                $coa = $piutang->coa_id ? Mst_coa::find($piutang->coa_id) : null;
                if ($piutang->journal_type=='P') {
                    $inv = Tx_invoice::find($piutang->inv_or_kwi_id);
                    if ($inv) $invList = collect([$inv]);
                    else {
                        // fallback by jurnal_date
                        $invList = Tx_invoice::whereDate('created_at',$piutang->jurnal_date)->limit(1)->get();
                    }
                } else {
                    $kw = Tx_kwitansi::find($piutang->inv_or_kwi_id);
                    if ($kw) $invList = collect([$kw]);
                }
                $sum = (float)$piutang->total;
            }
        }

        return view('tx.'.$this->folder.'.create', [
            'title'=>$this->title,'folder'=>$this->folder,'userLogin'=>$userLogin,'qCurrency'=>$qCurrency,'qVat'=>$qVat,
            'customerList'=>$customerList,'fCustomer'=>$fCustomer,'npwpOptions'=>['P','N'],'fNpwp'=>$fNpwp,
            'journalDates'=>$journalDates,'fJournalDate'=>$fJournalDate,
            'piutang'=>$piutang,'bayarVia'=>$bayarVia,'coa'=>$coa,'invList'=>$invList,'sum'=>$sum,
            'payment_mode_string'=>explode('|', (string)env('METHOD_BAYAR_SUPPLIER_NAME')),
            'payment_mode_id'=>explode('|', (string)env('METHOD_BAYAR_SUPPLIER_ID')),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['piutang_tmp_id'=>'required|numeric'], ['piutang_tmp_id.required'=>'Lengkapi Customer, NPWP, Journal Date']);
        $tmp = Tx_piutang_tmp::find($request->piutang_tmp_id);
        if(!$tmp) return redirect()->back()->with('status-error','Data piutang tmp tidak ditemukan.');
        if((int)date('Y',strtotime($tmp->jurnal_date))>=2026) return redirect()->back()->with('status-error','Jurnal date harus sebelum 2026.');

        $invNoTmp = $tmp->tipe_invoice=='I'
            ? DB::table('tx_invoices')->where('id',$tmp->inv_or_kwi_id)->value('invoice_no')
            : DB::table('tx_kwitansis')->where('id',$tmp->inv_or_kwi_id)->value('kwitansi_no');
        $exists = false;
        if($tmp->tipe_invoice=='I' && $invNoTmp){
            $exists = Tx_payment_receipt_invoice::where(['active'=>'Y','is_full_payment'=>'Y'])
                ->where('invoice_no',$invNoTmp)->where('invoice_no','LIKE','IN%')
                ->whereHas('payment_receipt', fn($q)=>$q->where('active','Y'))->exists();
        } elseif($tmp->tipe_invoice=='K' && $invNoTmp){
            $exists = Tx_payment_receipt_invoice::where(['active'=>'Y','is_full_payment'=>'Y'])
                ->where('invoice_no',$invNoTmp)->where('invoice_no','LIKE','KW%')
                ->whereHas('payment_receipt', fn($q)=>$q->where('active','Y'))->exists();
        } else {
            $exists = Tx_payment_receipt_invoice::where('invoice_id',$tmp->inv_or_kwi_id)->where(['active'=>'Y','is_full_payment'=>'Y'])->whereHas('payment_receipt', fn($q)=>$q->where('active','Y'))->exists();
        }
        if($exists) return redirect()->back()->with('status-error','Tagihan sudah dibayar.');

        $qCoa = Mst_coa::where('id',$tmp->coa_id)->where('is_cashflow','Y')->where('active','Y')->first();
        if(!$qCoa) return redirect()->back()->with('status-error','COA rekening bukan cashflow / tidak aktif.');

        DB::beginTransaction();
        try {
            // PA number by Journal Date year
            $yy = date_format(date_create($tmp->jurnal_date),"y");
            $prefix = env('P_PAYMENT_RECEIPT').$yy.'-';
            $maxAttempts=3; $attempt=0; $payment_receipt_no=null;
            while($attempt<$maxAttempts){
                try{
                    $last = Tx_payment_receipt::where('payment_receipt_no','LIKE',$prefix.'%')->where('payment_receipt_no','NOT LIKE','%Draft%')->where('active','Y')->selectRaw('CAST(REPLACE(payment_receipt_no,?, \'\') AS UNSIGNED) AS n',[$prefix])->orderBy('n','DESC')->lockForUpdate()->first();
                    $newInc = $last ? ((int)$last->n+1) : 1;
                    Auto_inc::where(['identity_name'=>'tx_payment_receipts'])->lockForUpdate()->first();
                    $existingAuto = Auto_inc::where(['identity_name'=>'tx_payment_receipts'])->first();
                    if($existingAuto) Auto_inc::where(['identity_name'=>'tx_payment_receipts'])->update(['id_auto_inc'=>$newInc,'updated_at'=>now()]);
                    else Auto_inc::create(['identity_name'=>'tx_payment_receipts','id_auto_inc'=>$newInc]);
                    $payment_receipt_no = $prefix.str_pad($newInc,5,'0',STR_PAD_LEFT);
                    if(Tx_payment_receipt::where('payment_receipt_no',$payment_receipt_no)->lockForUpdate()->exists()) throw new \Illuminate\Database\QueryException('mysql','Duplicate PA',[]);
                    break;
                }catch(\Illuminate\Database\QueryException $e){ $attempt++; if($attempt>=$maxAttempts) throw $e; usleep(100000*$attempt); }
            }
            if(!$payment_receipt_no) throw new \Exception('Gagal generate no PA');

            $qVat = Mst_global::where(['data_cat'=>'vat','active'=>'Y'])->first();
            $vatPct = ($tmp->journal_type=='P')?(float)($qVat->numeric_val ?? 0):0;
            $paymentTotal = ($tmp->journal_type=='P') ? round((float)$tmp->total/(1+$vatPct/100),2) : (float)$tmp->total;

            $customerId = Mst_customer::where('customer_unique_code',$tmp->kode_customer)->value('id');
            if(!$customerId) throw new \Exception('Customer '.$tmp->kode_customer.' tidak ada.');

            $ins = Tx_payment_receipt::create([
                'payment_receipt_no'=>$payment_receipt_no,
                'customer_id'=>$customerId,
                'payment_type_id'=>$tmp->journal_type,
                'payment_date'=>$tmp->jurnal_date,
                'payment_total'=>$paymentTotal,
                'payment_total_before_vat'=>$paymentTotal,
                'payment_total_after_vat'=>round($paymentTotal*(1+$vatPct/100),2),
                'diskon_pembelian'=>(float)$tmp->discount,
                'admin_bank'=>(float)$tmp->admin_bank,
                'biaya_kirim'=>(float)$tmp->biaya_kirim,
                'penerimaan_lainnya'=>(float)$tmp->penerimaan_lain,
                'payment_mode'=>$tmp->metode_bayar_id,
                'coa_id'=>$tmp->coa_id,
                'reference_no'=>$tmp->no_giro,
                'reference_date'=>$tmp->jurnal_date,
                'is_full_payment'=>'Y','is_draft'=>'N','active'=>'Y','remark'=>'Penerimaan Customer (Temp)',
                'created_by'=>Auth::user()->id,'updated_by'=>Auth::user()->id,
            ]);
            $receiptId=$ins->id;

            $inv = $tmp->journal_type=='P' ? Tx_invoice::find($tmp->inv_or_kwi_id) : Tx_kwitansi::find($tmp->inv_or_kwi_id);
            if(!$inv) throw new \Exception('Billing tidak ditemukan.');
            Tx_payment_receipt_invoice::create([
                'payment_receipt_id'=>$receiptId,
                'invoice_id'=>$inv->id,
                'invoice_no'=>$inv->invoice_no ?? $inv->kwitansi_no,
                'description'=>'Penerimaan Customer (Temp)',
                'total_payment'=>$paymentTotal,
                'total_payment_after_vat'=>round($paymentTotal*(1+$vatPct/100),2),
                'total_payment_full'=>$paymentTotal,
                'total_payment_full_after_vat'=>round($paymentTotal*(1+$vatPct/100),2),
                'is_full_payment'=>'Y','is_vat'=>($tmp->journal_type=='P'?'Y':'N'),'active'=>'Y',
                'created_by'=>Auth::user()->id,'updated_by'=>Auth::user()->id,
            ]);

            // approve 5
            $APPROVER_ID=5;
            $hasApprovedBy = Schema::hasColumn('tx_payment_receipts','approved_by');
            $hasApprovalAt = Schema::hasColumn('tx_payment_receipts','approval_at');
            $hasApprovedAt = Schema::hasColumn('tx_payment_receipts','approved_at');
            $updateData = ['updated_by'=>Auth::user()->id];
            if($hasApprovedBy) $updateData['approved_by']=$APPROVER_ID;
            if($hasApprovalAt) $updateData['approval_at']=now();
            elseif($hasApprovedAt) $updateData['approved_at']=now();
            else {
                // fallback: simpan di remark atau jangan block
            }
            if(count($updateData)>1) Tx_payment_receipt::where('id',$receiptId)->update($updateData);

            $ins->refresh();
            $qReceipt = $ins;

            $result = $this->createJournalForReceipt($qReceipt, $payment_receipt_no, $APPROVER_ID, $tmp);
            if(!$result['ok']) throw new \Exception('Jurnal tidak bisa dibentuk: '.$result['reason']);

            DB::commit();
            session()->flash('status','Penerimaan Customer (Temp) tersimpan dan ter-approve.');
            return redirect(env('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
        } catch(\Exception $e){
            DB::rollBack();
            if(str_contains($e->getMessage(),'Jurnal tidak bisa dibentuk')) return redirect()->back()->withInput()->with('status-error',$e->getMessage());
            if(str_contains($e->getMessage(),'Duplicate')) return redirect()->back()->withInput()->with('status-error','Nomor PA/Jurnal bentrok (race). Silakan coba Save lagi.');
            return redirect()->back()->withInput()->with('status-error', $e->getMessage());
        }
    }

    private function createJournalForReceipt($q, $payment_receipt_no, $APPROVER_ID, $tmp): array
    {
        try{
            $isPpn = ($q->payment_type_id=='P');
            $branch_id = null;
            // fallback branch dari invoice/kwitansi
            if($tmp->journal_type=='P'){
                $inv = Tx_invoice::find($tmp->inv_or_kwi_id);
                $branch_id = $inv->branch_id ?? null;
            } else {
                $kw = Tx_kwitansi::find($tmp->inv_or_kwi_id);
                $branch_id = $kw->branch_id ?? null;
            }
            if(!$branch_id) $branch_id = 1;
            $payment_mode = $q->payment_mode;
            $methodMap=['2'=>'Bank','3'=>'Advance Payment'];
            $methodNm=$methodMap[(string)$payment_mode] ?? 'Cash';
            $autoJournalId = $isPpn ? 7 : 14;
            $qAut = Mst_automatic_journal_detail::where(['auto_journal_id'=>$autoJournalId,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->first();
            if(!$qAut) return ['ok'=>false,'reason'=>'Setup Automatic Journal id '.$autoJournalId.' tidak ada untuk branch '.$branch_id.' metode '.$payment_mode.' ('.$methodNm.')'];

            $description = 'Penerimaan Customer (Temp) '.$payment_receipt_no;
            $total_cash = (float)$q->payment_total_after_vat + (float)$q->admin_bank + (float)$q->biaya_kirim + (float)$q->penerimaan_lainnya - (float)$q->diskon_pembelian;

            $maxAttempts=3; $attempt=0; $journal_no=null; $YearMonth=null;
            while($attempt<$maxAttempts){
                try{
                    $yearTemp = date_format(date_create($q->payment_date),"y");
                    $monthTemp = date_format(date_create($q->payment_date),"m");
                    $ymTemp = $yearTemp.$monthTemp;
                    $isGeneral = $isPpn;
                    $identityName = $isGeneral ? 'tx_general_journal' : 'tx_lokal_journal';
                    $prefix = $isGeneral ? env('P_GENERAL_JURNAL') : env('P_LOKAL_JURNAL');
                    $autoInc = Auto_inc::where(['identity_name'=>$identityName])->lockForUpdate()->first();
                    $newInc=1; $YearMonth=$yearTemp.$monthTemp;
                    if($autoInc){
                        $lastUpdAt = date_format(date_create($autoInc->updated_at),"ym");
                        $dateNow=date("ym");
                        if(($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)){
                            $lastCounterIfAny = $isGeneral
                                ? Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first()
                                : Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first();
                            if($lastCounterIfAny) $newInc=$lastCounterIfAny->lastCounter+1;
                        } else {
                            $newInc=(int)($autoInc->id_auto_inc ?:0)+1;
                            Auto_inc::where(['identity_name'=>$identityName])->update(['id_auto_inc'=>$newInc]);
                        }
                    } else {
                        $lastCounterIfAny = $isGeneral
                            ? Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first()
                            : Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.$prefix.$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')->whereRaw('general_journal_no LIKE \''.$prefix.$ymTemp.'%\'')->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')->where(['active'=>'Y'])->orderBy('general_journal_no','DESC')->first();
                        if($lastCounterIfAny) $newInc=$lastCounterIfAny->lastCounter+1;
                        Auto_inc::create(['identity_name'=>$identityName,'id_auto_inc'=>$newInc]);
                        $YearMonth=date('y').date('m');
                    }
                    $zero=str_repeat('0',max(0,5-strlen(strval($newInc))));
                    $journal_no=$prefix.$YearMonth.$zero.strval($newInc);
                    $exists=$isGeneral ? Tx_general_journal::where('general_journal_no',$journal_no)->lockForUpdate()->exists() : Tx_lokal_journal::where('general_journal_no',$journal_no)->lockForUpdate()->exists();
                    if($exists) throw new \Illuminate\Database\QueryException('mysql','Duplicate journal no',[]);
                    break;
                }catch(\Illuminate\Database\QueryException $e){ $attempt++; if($attempt>=$maxAttempts) throw $e; usleep(100000*$attempt); }
            }
            if(!$journal_no) return ['ok'=>false,'reason'=>'Gagal generate nomor jurnal'];

            if($isPpn){
                $insJournal = Tx_general_journal::create([
                    'general_journal_no'=>$journal_no,
                    'general_journal_date'=>$q->payment_date,
                    'total_debit'=>$total_cash,
                    'total_kredit'=>$total_cash,
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>7,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
                $journalId=$insJournal->id;
                // details - copy Approval 7
                $qHutang = Mst_automatic_journal_detail::where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'piutang\'')->first();
                $qBankAdmin = Mst_automatic_journal_detail::where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'bank admin\'')->first();
                $qDiscount = Mst_automatic_journal_detail::where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'discount\'')->first();
                $qBiayaKirim = Mst_automatic_journal_detail::where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'biaya kirim\'')->first();
                $qPenerimaan = Mst_automatic_journal_detail::where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')->first();
                $qCashExt = Mst_automatic_journal_detail_ext::select('coa_code_id')->where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'');
                $qCash = Mst_automatic_journal_detail::select('coa_code_id')->where(['auto_journal_id'=>7,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')->union($qCashExt)->first();

                Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qHutang->coa_code_id ?? $q->coa_id,'description'=>$description,'debit'=>0,'kredit'=>$q->payment_total_after_vat,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                if($qBankAdmin && $qBankAdmin->coa_code_id>0 && (float)$q->admin_bank>0) Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qBankAdmin->coa_code_id,'description'=>$description,'debit'=>0,'kredit'=>(float)$q->admin_bank,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                if((float)$q->diskon_pembelian>0 && $qDiscount) Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qDiscount->coa_code_id,'description'=>$description,'debit'=>(float)$q->diskon_pembelian,'kredit'=>0,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                if((float)$q->biaya_kirim>0 && $qBiayaKirim) Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qBiayaKirim->coa_code_id,'description'=>$description,'debit'=>0,'kredit'=>(float)$q->biaya_kirim,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                if((float)$q->penerimaan_lainnya>0 && $qPenerimaan) Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qPenerimaan->coa_code_id,'description'=>$description,'debit'=>(float)$q->penerimaan_lainnya,'kredit'=>0,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                Tx_general_journal_detail::create(['general_journal_id'=>$journalId,'coa_id'=>$qCash->coa_code_id ?? $q->coa_id,'description'=>$description,'debit'=>$total_cash,'kredit'=>0,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
            } else {
                $insJournal = Tx_lokal_journal::create([
                    'general_journal_no'=>$journal_no,
                    'general_journal_date'=>$q->payment_date,
                    'total_debit'=>$total_cash,
                    'total_kredit'=>$total_cash,
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>14,
                    'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID,
                ]);
                $journalId=$insJournal->id;
                $qHutang = Mst_automatic_journal_detail::where(['auto_journal_id'=>14,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'piutang\'')->first();
                $qBankAdmin = Mst_automatic_journal_detail::where(['auto_journal_id'=>14,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\'bank admin\'')->first();
                Tx_lokal_journal_detail::create(['lokal_journal_id'=>$journalId,'coa_id'=>$qHutang->coa_code_id ?? $q->coa_id,'description'=>$description,'debit'=>0,'kredit'=>$q->payment_total_after_vat,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                if($qBankAdmin && $qBankAdmin->coa_code_id>0 && (float)$q->admin_bank>0) Tx_lokal_journal_detail::create(['lokal_journal_id'=>$journalId,'coa_id'=>$qBankAdmin->coa_code_id,'description'=>$description,'debit'=>0,'kredit'=>(float)$q->admin_bank,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
                $qCashExt = Mst_automatic_journal_detail_ext::select('coa_code_id')->where(['auto_journal_id'=>14,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'');
                $qCash = Mst_automatic_journal_detail::select('coa_code_id')->where(['auto_journal_id'=>14,'branch_id'=>$branch_id,'method_id'=>$payment_mode,'coa_code_id'=>$q->coa_id,'active'=>'Y'])->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')->union($qCashExt)->first();
                Tx_lokal_journal_detail::create(['lokal_journal_id'=>$journalId,'coa_id'=>$qCash->coa_code_id ?? $q->coa_id,'description'=>$description,'debit'=>$total_cash,'kredit'=>0,'active'=>'Y','created_by'=>$APPROVER_ID,'updated_by'=>$APPROVER_ID]);
            }
            return ['ok'=>true,'reason'=>''];
        }catch(\Exception $e){
            if(str_contains($e->getMessage(),'Duplicate journal')) throw new \Illuminate\Database\QueryException('mysql','Duplicate journal no',[]);
            return ['ok'=>false,'reason'=>$e->getMessage()];
        }
    }
}
