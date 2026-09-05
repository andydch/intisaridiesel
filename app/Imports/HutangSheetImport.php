<?php

namespace App\Imports;

use App\Imports\Support\DocNumber;
use App\Imports\Support\KodeWarisan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;

class HutangSheetImport implements ToCollection
{
    public int $roCount = 0;
    public int $ctsCount = 0;
    public int $skipped2026 = 0;
    public ?string $roPertama = null;
    public ?string $roTerakhir = null;
    public ?string $ctsPertama = null;
    public ?string $ctsTerakhir = null;
    /** @var string[] */
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        // v2 HUTANG: A1 = Kode Supplier
        if ($rows->isEmpty() || strtolower(trim((string)($rows[0][0] ?? ''))) !== 'kode supplier') {
            throw ValidationException::withMessages(['file' => ['Template HUTANG v2 tidak sesuai: A1 harus Kode Supplier']]);
        }
        // Waris kolom A (Kode Supplier) berantai
        $rows = KodeWarisan::fill($rows, 0);

        $terurai = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            if ($i < 1) continue;
            $arr = is_array($row) ? $row : $row->toArray();
            $v = HutangRowValidator::validate($arr, $i + 1);
            if ($v->skip) continue;
            if (! $v->ok) { $errors[] = $v->error; continue; }
            // Filter tahun: jika RO Date year >= 2026 skip? Spec tidak sebutkan 2026 untuk v2, tapi pertahankan jika ada
            // Untuk v2, semua tanggal diproses (tidak ada filter 2026 di spec baru) — jadi jangan skip
            $terurai[] = ['d' => $v->data, 'n' => $i + 1, 'raw' => $arr];
        }
        foreach ($errors as $e) $this->gagal[] = 'Sheet #1 - ' . $e;
        // ---- Validasi konsistensi: Q (Kode Transak) sama → S (Journal Date) harus sama ----
        if (!empty($terurai)) {
            $mapQtoS = []; $barisPerQ = []; $qRawDisplay = [];
            foreach ($terurai as $item) {
                $d = $item['d']; $n = $item['n'];
                $qNorm = strtolower(preg_replace('/\s+/', ' ', trim((string)$d->kodeTrans)));
                if ($qNorm === '') continue;
                $sKey = $d->jurnalDate ? $d->jurnalDate->format('Y-m-d') : '__EMPTY__';
                $mapQtoS[$qNorm][$sKey] = true;
                $barisPerQ[$qNorm][] = $n;
                $qRawDisplay[$qNorm] = $d->kodeTrans;
            }
            $konsistensiGagal = [];
            foreach ($mapQtoS as $qNorm => $sKeys) {
                if (count($sKeys) > 1) {
                    $barisList = implode(', ', $barisPerQ[$qNorm]);
                    $qDisp = $qRawDisplay[$qNorm];
                    $konsistensiGagal[] = "Sheet #1 - Kode Transak '{$qDisp}' memiliki Journal Date berbeda pada baris {$barisList} — harus sama.";
                }
            }
            if (!empty($konsistensiGagal)) {
                foreach ($konsistensiGagal as $e) $this->gagal[] = $e;
                return;
            }
        }
        if (! $terurai) return;

        // Pre-flight: supplier ada & aktif, branch initial, currency, coa, metode, bayar via
        $layak = [];
        foreach ($terurai as $item) {
            $d = $item['d']; $n = $item['n']; $raw = $item['raw'];
            $sup = DB::table('mst_suppliers')->where(['supplier_code' => $d->supplierCode, 'active' => 'Y'])->first();
            if (! $sup) { $this->gagal[] = "Sheet #1 - Baris {$n}: Supplier '{$d->supplierCode}' tidak ditemukan/aktif"; continue; }
            // K: inisial cabang harus sesuai mst_branches (initial atau name)
            $br = DB::table('mst_branches')->where('initial', $d->branchName)->orWhere('name', 'LIKE', "%{$d->branchName}%")->where('active','Y')->first();
            if (! $br) {
                // coba where initial saja
                $br = DB::table('mst_branches')->where('initial', $d->branchName)->where('active','Y')->first();
            }
            if (! $br) { $this->gagal[] = "Sheet #1 - Baris {$n}: Branch '{$d->branchName}' tidak ditemukan/aktif"; continue; }
            $cur = DB::table('mst_globals')->where(['data_cat'=>'currency','active'=>'Y'])->where('title_ind', $d->currencyKode==='RP'?'Rupiah':'Dollar Amerika Serikat')->first();
            if (! $cur) { $this->gagal[] = "Sheet #1 - Baris {$n}: Currency '{$d->currencyKode}' tidak dikenali"; continue; }
            $coa = DB::table('mst_coas')->where(['coa_code_complete'=>$d->coaCode,'active'=>'Y'])->first();
            if (! $coa) { $this->gagal[] = "Sheet #1 - Baris {$n}: COA '{$d->coaCode}' tidak ditemukan/aktif"; continue; }
            // O metode bayar
            $metodeName = trim((string)($raw[14] ?? ''));
            $allowedNames = array_map('trim', explode('|', (string)env('METHOD_BAYAR_SUPPLIER_NAME','Cash|Bank|Advance Payment')));
            $allowedIds = array_map('trim', explode('|', (string)env('METHOD_BAYAR_SUPPLIER_ID','1|2|3')));
            // case-insensitive search
            $metodeIdx = array_search(strtolower($metodeName), array_map('strtolower', $allowedNames), true);
            if ($metodeIdx === false) { $this->gagal[] = "Sheet #1 - Baris {$n}: Metode Bayar '{$metodeName}' tidak sesuai env"; continue; }
            $metodeId = (int)($allowedIds[$metodeIdx] ?? 0);
            // P bayar via
            $bayarViaName = trim((string)($raw[15] ?? ''));
            $bayarVia = null;
            if ($bayarViaName !== '') {
                $bayarVia = DB::table('mst_globals')->where(['data_cat'=>'payment-ref','active'=>'Y'])->where('title_ind','LIKE',"%{$bayarViaName}%")->first();
                if (! $bayarVia) $bayarVia = DB::table('mst_globals')->where(['data_cat'=>'payment-ref','active'=>'Y'])->where('title_ind',$bayarViaName)->first();
            }
            $layak[] = (object)['d'=>$d,'n'=>$n,'raw'=>$raw,'sup'=>$sup,'br'=>$br,'cur'=>$cur,'coa'=>$coa,'metodeId'=>$metodeId,'bayarVia'=>$bayarVia,'sortKey'=>[$d->tanggal->timestamp,$n]];
        }
        if (! $layak) return;
        // Sort termuda dulu
        usort($layak, fn($a,$b)=> $a->sortKey <=> $b->sortKey);

        // Atomic via outer DB::transaction (controller) — tidak pakai inner beginTransaction agar rollback total jika sheet lain gagal
        try {
            $ctx = [];
            $supplierSums = []; // untuk beginning_balance
            foreach ($layak as $item) {
                $d = $item->d; $sup=$item->sup; $br=$item->br; $cur=$item->cur;
                $tipe11 = (int)$sup->supplier_type_id === 11;
                // Penomoran RO: ROM + yy
                $year2 = $d->tanggal->format('y');
                $prefix = env('P_RECEIPT_ORDER').$year2.'-'; // ROM25-
                $noRo = DocNumber::nextNo('tx_receipt_orders','receipt_no',$prefix);
                $vat_val = $d->dppF >0 ? $d->ppnG*100/$d->dppF : 0;
                DB::table('tx_receipt_orders')->insert([
                    'receipt_no'=>$noRo,
                    'receipt_date'=>$d->tanggal->toDateString(),
                    'po_or_pm_no'=>'no PO/MO',
                    'journal_type_id'=>$d->journalType,
                    'supplier_id'=>$sup->id,
                    'supplier_type_id'=>$sup->supplier_type_id,
                    'supplier_entity_type_id'=>$sup->entity_type_id,
                    'supplier_name'=>$sup->name,
                    'currency_id'=>$cur->id,
                    'total_qty'=>0,
                    'total_before_vat'=> $tipe11 ? $d->dppF : $d->dppI,
                    'total_before_vat_rp'=>$d->dppF,
                    'total_vat'=> $tipe11 ? $d->ppnG : $d->ppnJ,
                    'total_vat_rp'=>$d->ppnG,
                    'total_after_vat'=> $tipe11 ? $d->dppPpnH : $d->dppPpnK,
                    'total_after_vat_rp'=>$d->dppPpnH,
                    'branch_id'=>$br->id,
                    'courier_id'=>null,
                    'courier_type'=>null,
                    'invoice_no'=>$d->invoiceNo ?? $noRo,
                    'invoice_amount'=>0,
                    'exchange_rate'=>0,
                    'exc_rate_for_vat'=>0,
                    'bea_masuk'=>0,
                    'import_shipping_cost'=>0,
                    'bl_no'=>null,
                    'vessel_no'=>null,
                    'weight_type_id01'=>null,
                    'weight_type_id02'=>null,
                    'gross_weight'=>null,
                    'measurement'=>0,
                    'remark'=>null,
                    'vat_val'=>$vat_val,
                    'approved_by'=>null,
                    'approved_at'=>null,
                    'canceled_by'=>null,
                    'canceled_at'=>null,
                    'draft_at'=>null,
                    'draft_to_created_at'=>null,
                    'is_draft'=>'N',
                    'active'=>'Y',
                    'created_by'=>1,
                    'updated_by'=>1,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
                $roId = (int)DB::getPdo()->lastInsertId();
                $ctx[] = (object)['roId'=>$roId,'noRo'=>$noRo,'d'=>$d,'sup'=>$sup,'br'=>$br,'cur'=>$cur,'coa'=>$item->coa,'metodeId'=>$item->metodeId,'bayarVia'=>$item->bayarVia,'raw'=>$item->raw,'key'=> $d->supplierCode.'|'.$d->journalType.'|'.$d->kodeTrans];
                $this->roCount++; $this->roPertama ??= $noRo; $this->roTerakhir=$noRo;
                // akumulasi beginning_balance per supplier (kolom G)
                $supplierSums[$d->supplierCode] = ($supplierSums[$d->supplierCode] ?? 0) + $d->dppPpnH;
            }

            // Buat CTS per grup (supplier|journal|kodeTrans)
            $groups = []; foreach($ctx as $c){ $groups[$c->key][]=$c; }
            // Urut grup by CTS Plan Date termuda? Spec tidak sebutkan, tapi pakai planDate
            uasort($groups, fn($a,$b)=> [$a[0]->d->planDate->timestamp,$a[0]->d->tanggal->timestamp] <=> [$b[0]->d->planDate->timestamp,$b[0]->d->tanggal->timestamp]);

            foreach ($groups as $members) {
                $m0 = $members[0];
                $year2 = $m0->d->planDate->format('y');
                $prefix = env('P_TAGIHAN_SUPPLIER').$year2.'-'; // TSM25-
                $noCts = DocNumber::nextNo('tx_tagihan_suppliers','tagihan_supplier_no',$prefix);
                $totF = array_sum(array_map(fn($m)=>$m->d->dppF,$members));
                $totG = array_sum(array_map(fn($m)=>$m->d->ppnG,$members));
                $totH = array_sum(array_map(fn($m)=>$m->d->dppPpnH,$members));
                $bankId = DB::table('mst_coas')->where('coa_code_complete',$m0->d->coaCode)->value('id');
                DB::table('tx_tagihan_suppliers')->insert([
                    'tagihan_supplier_no'=>$noCts,
                    'tagihan_supplier_date'=>$m0->d->planDate->toDateString(),
                    'supplier_id'=> $m0->sup->id,
                    'total_price'=>$totF,
                    'total_price_vat'=>$totG,
                    'grandtotal_price'=>$totH,
                    'is_vat'=> $m0->d->journalType==='P'?'Y':'N',
                    'bank_id'=>$bankId,
                    'active'=>'Y',
                    'created_by'=>1,
                    'updated_by'=>1,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
                $ctsId = (int)DB::getPdo()->lastInsertId();
                foreach ($members as $m) {
                    DB::table('tx_tagihan_supplier_details')->insert([
                        'tagihan_supplier_id'=>$ctsId,
                        'receipt_order_id'=>$m->roId,
                        'total_price_per_ro'=>$m->d->dppPpnH, // kolom G
                        'is_vat_per_ro'=> $m->d->journalType==='P'?'Y':'N',
                        'active'=>'Y',
                        'created_by'=>1,
                        'updated_by'=>1,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ]);
                }
                $this->ctsCount++; $this->ctsPertama ??= $noCts; $this->ctsTerakhir=$noCts;

                // Insert tx_hutang_tmp (1 CTS = 1 row di tmp, unique)
                $m0raw = $m0->raw;
                $jurnalDate = $m0->d->jurnalDate ? $m0->d->jurnalDate->toDateString() : null;
                // Jika S kosong, null
                if (isset($m0raw[18]) && trim((string)$m0raw[18])==='') $jurnalDate = null;
                DB::table('tx_hutang_tmp')->insert([
                    'kode_supplier'=>$m0->d->supplierCode,
                    'cts_id'=>$ctsId,
                    'journal_type'=>$m0->d->journalType,
                    'total'=>$totH,
                    'branch_id'=>$m0->br->id,
                    'coa_id'=>$bankId,
                    'metode_bayar_id'=>$m0->metodeId,
                    'bayar_via_id'=>$m0->bayarVia?->id,
                    'no_giro'=> trim((string)($m0raw[17] ?? '')) ?: null, // R
                    'jurnal_date'=>$jurnalDate, // S
                    'admin_bank'=> $m0->d->adminBank ?? 0, // T
                    'biaya_asuransi'=> $m0->d->biayaAss ?? 0, // U
                    'biaya_kirim'=> $m0->d->biayaKirim ?? 0, // V
                    'biaya_lain'=> $m0->d->biayaLain ?? 0, // W
                    'discount'=> $m0->d->discount ?? 0, // X
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            // Update beginning_balance per supplier (overwrite ΣG — bukan COALESCE +)
            foreach ($supplierSums as $kode=>$sumG) {
                $sumG = round((float)$sumG, 2);
                DB::table('mst_suppliers')->where('supplier_code',$kode)->update([
                    'beginning_balance'=> DB::raw(number_format($sumG,2,'.',''))
                ]);
            }

        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['file'=>['Kesalahan Hutang: '.$e->getMessage()]]);
        }
    }
}
