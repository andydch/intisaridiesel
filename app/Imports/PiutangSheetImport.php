<?php

namespace App\Imports;

use App\Imports\Support\DocNumber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;

class PiutangSheetImport implements ToCollection
{
    /** Judul kolom wajib — pelindung anti salah-posisi kolom */
    private const HEADER_WAJIB_IDX7 = 'PPN/NON PPN';

    public int $invCount = 0;
    public int $kwiCount = 0;
    public int $skipped2026 = 0;
    public ?string $noPertama = null;
    public ?string $noTerakhir = null;
    /** @var string[] catatan baris yang tidak dapat diproses */
    public array $gagal = [];

    public function collection(Collection $rows): void
    {
        $rows = $rows->values();
        if ($rows->isEmpty() || trim((string) ($rows[0][7] ?? '')) !== self::HEADER_WAJIB_IDX7) {
            throw ValidationException::withMessages(['file' => [
                'Template tidak sesuai: worksheet kartu-piutang tidak memiliki kolom "PPN/NON PPN" di posisi H. File tidak diproses.']]);
        }

        $terurai = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            if ($i < 1) continue;                                    // baris 1 = header diabaikan (file asli: data mulai baris 2)
            $v = PiutangRowValidator::validate(is_array($row) ? $row : $row->toArray(), $i + 1);
            if ($v->skip) continue;
            if (! $v->ok) { $errors[] = $v->error; continue; }       // format salah -> catatan gagal
            if ($v->data->tanggal->year >= 2026) { $this->skipped2026++; continue; }   // ATURAN: hanya < 2026
            $terurai[] = ['d' => $v->data, 'n' => $i + 1];
        }
        foreach ($errors as $e) { $this->gagal[] = 'Sheet #2 - ' . $e; }
        if (! $terurai) return;

        // ---- PRE-FLIGHT resolusi referensi (fitur #1): customer wajib ADA & AKTIF ----
        $layak = [];
        foreach ($terurai as $item) {
            $d = $item['d'];
            $n = $item['n'];

            $cust = DB::table('mst_customers')->where(['customer_unique_code' => $d->customerCode, 'active' => 'Y'])->first();
            if (! $cust) {
                $this->gagal[] = "Sheet #2 - Baris {$n}: Customer '{$d->customerCode}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $br = DB::table('mst_branches')->where('name', 'LIKE', '%' . $d->branchName . '%')->where('active', 'Y')->first();
            if (! $br) {
                $this->gagal[] = "Sheet #2 - Baris {$n}: Branch '{$d->branchName}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $coa = DB::table('mst_coas')->where(['coa_code_complete' => $d->coaCode, 'active' => 'Y'])->first();
            if (! $coa) {
                $this->gagal[] = "Sheet #2 - Baris {$n}: CoA '{$d->coaCode}' tidak ditemukan atau tidak aktif";
                continue;
            }

            $layak[] = (object) ['d' => $d, 'n' => $n, 'cust' => $cust, 'br' => $br, 'coa' => $coa,
                                 'sortKey' => [$d->tanggal->timestamp, $n]];   // ASC (terlama dulu)
        }
        usort($layak, fn ($a, $b) => $a->sortKey <=> $b->sortKey);
        if (! $layak) return;

        DB::beginTransaction();
        try {
            foreach ($layak as $item) {
                $d = $item->d;
                $cust = $item->cust;
                $br = $item->br;
                $coa = $item->coa;

                $top = (int) ($cust->top ?? 0);
                $expired = $d->tanggal->copy()->addDays($top)->toDateString();
                $audit = ['created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];
                $nonaktif = ['approved_by' => null, 'approved_at' => null, 'canceled_by' => null,
                             'canceled_at' => null, 'draft_at' => null, 'draft_to_created_at' => null];

                if ($d->jenis === 'P') {
                    $noInv = DocNumber::nextNo('tx_invoices', 'invoice_no',
                        (string) env('P_INVOICE') . $d->tanggal->format('y') . '-');
                    DB::table('tx_invoices')->insert(array_merge([
                        'invoice_no'           => $noInv,
                        'tax_invoice_no'       => null,
                        'customer_id'          => $cust->id,
                        'delivery_order_id'    => null,
                        'invoice_date'         => $d->tanggal->toDateString(),
                        'invoice_expired_date' => $expired,
                        'tax_invoice_date'     => null,
                        'branch_id'            => $br->id,
                        'payment_to_id'        => $coa->id,
                        'do_total'             => $d->dppE,
                        'do_vat'               => $d->ppnF,
                        'do_grandtotal_vat'    => $d->totalG,
                        'remark'               => null,
                        'header'               => null,
                        'footer'               => null,
                        'vat_val'              => $d->dppE > 0 ? $d->ppnF * 100 / $d->dppE : 0,
                        'is_draft'             => 'N',
                        'active'               => 'Y',
                    ], $nonaktif, $audit));
                    $no = $noInv;
                    $this->invCount++;
                } else {
                    $noKwi = DocNumber::nextNo('tx_kwitansis', 'kwitansi_no',
                        (string) env('P_KWITANSI') . $d->tanggal->format('y') . '-');
                    DB::table('tx_kwitansis')->insert(array_merge([
                        'kwitansi_no'           => $noKwi,
                        'customer_id'           => $cust->id,
                        'kwitansi_date'         => $d->tanggal->toDateString(),
                        'kwitansi_expired_date' => $expired,
                        'branch_id'             => $br->id,
                        'payment_to_id'         => $coa->id,
                        'np_total'              => $d->dppE,
                        'remark'                => null,
                        'header'                => null,
                        'footer'                => null,
                        'is_draft'              => 'N',
                        'active'                => 'Y',
                    ], $nonaktif, $audit));
                    $no = $noKwi;
                    $this->kwiCount++;
                }
                $this->noPertama ??= $no;
                $this->noTerakhir = $no;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();   // error tak terduga = batalkan seluruh penulisan
            throw ValidationException::withMessages(['file' => ['Kesalahan tak terduga: ' . $e->getMessage()]]);
        }
    }
}
