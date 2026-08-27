<?php

namespace App\Imports;

use App\Imports\Support\DocNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;

class HutangSheetImport implements ToCollection
{
    /** Judul kolom wajib (layout revisi) — pelindung anti salah-posisi kolom */
    private const HEADER_WAJIB_IDX12 = 'Invoice No';

    public int $roCount = 0;
    public int $ctsCount = 0;
    public int $skipped2026 = 0;
    public ?string $roPertama = null;
    public ?string $roTerakhir = null;
    public ?string $ctsPertama = null;
    public ?string $ctsTerakhir = null;
    /** @var string[] catatan baris yang tidak dapat diproses */
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        if ($rows->isEmpty() || trim((string) ($rows[0][12] ?? '')) !== self::HEADER_WAJIB_IDX12) {
            throw ValidationException::withMessages(['file' => [
                'Template tidak sesuai: kolom M (index 12) wajib berjudul "Invoice No" (layout revisi). File tidak diproses.']]);
        }

        $terurai = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            if ($i < 1) continue;                                    // baris 1 = header diabaikan (file asli: data mulai baris 2)
            $v = HutangRowValidator::validate(is_array($row) ? $row : $row->toArray(), $i + 1);
            if ($v->skip) continue;
            if (! $v->ok) { $errors[] = $v->error; continue; }       // format salah -> catatan gagal
            if ($v->data->tanggal->year >= 2026) { $this->skipped2026++; continue; }   // ATURAN: hanya < 2026
            $terurai[] = ['d' => $v->data, 'n' => $i + 1];
        }
        foreach ($errors as $e) { $this->gagal[] = 'Sheet #1 - ' . $e; }
        if (! $terurai) return;

        // ---- PRE-FLIGHT resolusi referensi (fitur #1): gagal lookup = catat, JANGAN hentikan ----
        $layak = [];
        foreach ($terurai as $item) {
            $d = $item['d'];
            $n = $item['n'];

            $sup = DB::table('mst_suppliers')->where(['supplier_code' => $d->supplierCode, 'active' => 'Y'])->first();
            if (! $sup) {
                $this->gagal[] = "Sheet #1 - Baris {$n}: Supplier '{$d->supplierCode}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $br = DB::table('mst_branches')->where('name', 'LIKE', '%' . $d->branchName . '%')->where('active', 'Y')->first();
            if (! $br) {
                $this->gagal[] = "Sheet #1 - Baris {$n}: Branch '{$d->branchName}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $cur = DB::table('mst_globals')->where(['data_cat' => 'currency', 'active' => 'Y'])
                ->where('title_ind', $d->currencyKode === 'RP' ? 'Rupiah' : 'Dollar Amerika Serikat')->first();
            if (! $cur) {
                $this->gagal[] = "Sheet #1 - Baris {$n}: Currency '{$d->currencyKode}' tidak dikenali";
                continue;
            }

            $coa = DB::table('mst_coas')->where(['coa_code_complete' => $d->coaCode, 'active' => 'Y'])->first();
            if (! $coa) {
                $this->gagal[] = "Sheet #1 - Baris {$n}: CoA '{$d->coaCode}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $layak[] = (object) ['d' => $d, 'n' => $n, 'sup' => $sup, 'br' => $br, 'cur' => $cur,
                                 'sortKey' => [$d->tanggal->timestamp, $n]];   // urutan ASC (terlama dulu)
        }
        usort($layak, fn ($a, $b) => $a->sortKey <=> $b->sortKey);
        if (! $layak) return;

        DB::beginTransaction();
        try {
            $ctx = [];   // konteks tiap RO utk pembentukan CTS
            foreach ($layak as $item) {
                $d = $item->d;
                $sup = $item->sup;
                $br = $item->br;
                $cur = $item->cur;

                $tipe11 = (int) $sup->supplier_type_id === 11;
                $noRo = DocNumber::nextNo('tx_receipt_orders', 'receipt_no',
                    (string) env('P_RECEIPT_ORDER') . $d->tanggal->format('y') . '-');

                DB::table('tx_receipt_orders')->insert([
                    'receipt_no'             => $noRo,
                    'receipt_date'           => $d->tanggal->toDateString(),
                    'po_or_pm_no'            => 'no PO/MO',
                    'journal_type_id'        => $d->journalType,
                    'supplier_id'            => $sup->id,
                    'supplier_type_id'       => $sup->supplier_type_id,
                    'supplier_entity_type_id'=> $sup->entity_type_id,
                    'supplier_name'          => $sup->name,
                    'currency_id'            => $cur->id,
                    'total_qty'              => 0,
                    'total_before_vat'       => $tipe11 ? $d->dppF : $d->dppI,
                    'total_before_vat_rp'    => $d->dppF,
                    'total_vat'              => $tipe11 ? $d->ppnG : $d->ppnJ,
                    'total_vat_rp'           => $d->ppnG,
                    'total_after_vat'        => $tipe11 ? $d->dppPpnH : $d->dppPpnK,
                    'total_after_vat_rp'     => $d->dppPpnH,
                    'branch_id'              => $br->id,
                    'courier_id'             => null,
                    'courier_type'           => null,
                    'invoice_no'             => $d->invoiceNo ?? $noRo,
                    'invoice_amount'         => 0,
                    'exchange_rate'          => 0,
                    'exc_rate_for_vat'       => 0,
                    'bea_masuk'              => 0,
                    'import_shipping_cost'   => 0,
                    'bl_no'                  => null,
                    'vessel_no'              => null,
                    'weight_type_id01'       => null,
                    'weight_type_id02'       => null,
                    'gross_weight'           => null,
                    'measurement'            => 0,
                    'remark'                 => null,
                    'vat_val'                => $d->dppF > 0 ? $d->ppnG * 100 / $d->dppF : 0,
                    'approved_by'            => null,
                    'approved_at'            => null,
                    'canceled_by'            => null,
                    'canceled_at'            => null,
                    'draft_at'               => null,
                    'draft_to_created_at'    => null,
                    'is_draft'               => 'N',
                    'active'                 => 'Y',
                    'created_by'             => 1,
                    'updated_by'             => 1,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
                $roId = (int) DB::getPdo()->lastInsertId();

                $ctx[] = (object) [
                    'roId' => $roId,
                    'noRo' => $noRo,
                    'd'    => $d,
                    'key'  => implode('|', [$d->supplierCode, $d->journalType, $d->kodeTrans, $d->coaCode, $d->planDate->toDateString()]),
                ];
                $this->roCount++;
                $this->roPertama ??= $noRo;
                $this->roTerakhir = $noRo;
            }

            // kelompokkan CTS; urut grup berdasarkan plan date terkecil lalu tanggal RO baris pertama
            $groups = [];
            foreach ($ctx as $c) { $groups[$c->key][] = $c; }
            uasort($groups, fn ($a, $b) => [$a[0]->d->planDate->timestamp, $a[0]->d->tanggal->timestamp]
                                         <=> [$b[0]->d->planDate->timestamp, $b[0]->d->tanggal->timestamp]);

            foreach ($groups as $members) {
                $m0 = $members[0];
                $noCts = DocNumber::nextNo('tx_tagihan_suppliers', 'tagihan_supplier_no',
                    (string) env('P_TAGIHAN_SUPPLIER') . $m0->d->planDate->format('y') . '-');
                $bankId = DB::table('mst_coas')->where('coa_code_complete', $m0->d->coaCode)->value('id');

                $totF = array_sum(array_map(fn ($m) => $m->d->dppF, $members));
                $totG = array_sum(array_map(fn ($m) => $m->d->ppnG, $members));
                $totH = array_sum(array_map(fn ($m) => $m->d->dppPpnH, $members));

                DB::table('tx_tagihan_suppliers')->insert([
                    'tagihan_supplier_no'   => $noCts,
                    'tagihan_supplier_date' => $m0->d->planDate->toDateString(),
                    'supplier_id'           => DB::table('tx_receipt_orders')->where('id', $members[0]->roId)->value('supplier_id'),
                    'total_price'           => $totF,
                    'total_price_vat'       => $totG,
                    'grandtotal_price'      => $totH,
                    'is_vat'                => $totG > 0 ? 'Y' : 'N',
                    'bank_id'               => $bankId,
                    'active'                => 'Y',
                    'created_by'            => 1,
                    'updated_by'            => 1,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
                $ctsId = (int) DB::getPdo()->lastInsertId();

                foreach ($members as $m) {
                    DB::table('tx_tagihan_supplier_details')->insert([
                        'tagihan_supplier_id' => $ctsId,
                        'receipt_order_id'    => $m->roId,
                        'total_price_per_ro'  => $m->d->dppF,
                        'is_vat_per_ro'       => $m->d->ppnG > 0 ? 'Y' : 'N',
                        'active'              => 'Y',
                        'created_by'          => 1,
                        'updated_by'          => 1,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
                $this->ctsCount++;
                $this->ctsPertama ??= $noCts;
                $this->ctsTerakhir = $noCts;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();   // error tak terduga = batalkan seluruh penulisan (baris gagal tetap tercatat di atas)
            throw ValidationException::withMessages(['file' => ['Kesalahan tak terduga: ' . $e->getMessage()]]);
        }
    }
}
