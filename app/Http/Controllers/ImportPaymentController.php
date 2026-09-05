<?php

namespace App\Http\Controllers;

use App\Imports\FailureReport;
use App\Imports\HutangPiutangImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ImportPaymentController extends Controller
{
    /** Gate akses: hanya andydch@koidigital.co.id & maeger@koidigital.co.id */
    private function gate(): void
    {
        abort_unless(Auth::check() && in_array(Auth::user()->email, ['andydch@koidigital.co.id', 'maeger@koidigital.co.id'], true), 403, 'Akses ditolak.');
    }

    public function page()
    {
        $this->gate();
        return view('import-payment.index');
    }

    /** Unduh berkas catatan kegagalan (hanya file yang dibuat helper ini). */
    public function report(string $filename)
    {
        $this->gate();
        $path = FailureReport::path($filename);
        abort_if ($path === null, 404, 'Berkas catatan tidak ditemukan.');
        return response()->download($path, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Unduh template Excel untuk diisi data lalu di-import kembali.
     * Judul kolom & nama sheet = kontrak importer (pelindung anti salah-posisi).
     * Sheet 1 & 2 sudah berisi contoh data agar user paham format yang benar.
     */
    public function template()
    {
        $this->gate();

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Fetch 1-2 kode aktif untuk contoh (fallback dummy jika kosong — untuk pesan error yang jelas)
        $sup1 = DB::table('mst_suppliers')->where('active', 'Y')->orderBy('id')->value('supplier_code') ?? 'SUP-001';
        $sup2 = DB::table('mst_suppliers')->where('active', 'Y')->orderBy('id', 'desc')->value('supplier_code') ?? $sup1;
        $cust1 = DB::table('mst_customers')->where('active', 'Y')->orderBy('id')->value('customer_unique_code') ?? 'CUST-001';
        $cust2 = DB::table('mst_customers')->where('active', 'Y')->orderBy('id', 'desc')->value('customer_unique_code') ?? $cust1;
        $branch1 = DB::table('mst_branches')->where('active', 'Y')->orderBy('id')->value('name') ?? 'Jakarta';
        $branch2 = DB::table('mst_branches')->where('active', 'Y')->orderBy('id', 'desc')->value('name') ?? $branch1;
        $coa1 = DB::table('mst_coas')->where('active', 'Y')->orderBy('id')->value('coa_code_complete') ?? '11201';
        $coa2 = DB::table('mst_coas')->where('active', 'Y')->where('coa_code_complete', '!=', $coa1)->orderBy('id')->value('coa_code_complete') ?? $coa1;
        // Pastikan contoh M/J = 11211 selalu valid
        $coa11211 = DB::table('mst_coas')->where('coa_code_complete','11211')->where('active','Y')->value('coa_code_complete') ?? '11211';

        $hutang = $ss->getActiveSheet();
        $hutang->setTitle('HUTANG');
        $hutang->fromArray([['Kode Supplier','RO Date','Journal Type','Currency','DPP','PPN','TOT','DPP FOB','PPN FOB','TOT FOB','INT Branch','Inv. No','CTS/COA CODE','CTS PLAN DATE','METODE BAYAR','BAYAR VIA','KODE TRANSAK','NO GIRO','JOURNAL DATE','ADM BANK','BIAYA ASS','BIAYA KIRIM','BIAYA LAIN','DISC']], null, 'A1');
        // Contoh 3 baris HUTANG (valid, <2026, S wajib, Q sama → S sama)
        $hutang->fromArray([
            [$sup1, '15/05/2025', 'P', 'Rupiah', 10000000, 1100000, 11100000, 10000000, 1100000, 11100000, $branch1, 'INV-2025-001', '11211', '20/06/2025', 'Bank', 'Giro', 'TRX-001', 'G-001', '16/05/2025', 500, 200, 150, 300, 2500],
            [$sup2, '20/06/2025', 'N', 'Rupiah', 5000, 0, 5000, 5000, 0, 5000, $branch2, 'abc 002', '11211', '25/07/2025', 'Cash', 'Cash', 'TRX-002', '', '21/06/2025', 0, 0, 0, 0, 0],
            [$sup1, '10/12/2025', 'P', 'Rupiah', 2500000, 275000, 2775000, 2500000, 275000, 2775000, $branch1, 'INV-2025-003', '11211', '15/12/2025', 'Advance Payment', 'Transfer', 'TRX-003', 'G-003', '11/12/2025', 1000, 0, 500, 0, 100],
        ], null, 'A2');

        $piutang = $ss->createSheet();
        $piutang->setTitle('PIUTANG');
        $piutang->fromArray([['Kode Cust','Invoice Date','INT BRANCH','DPP','PPN','TOT','JOURNAL TYPE','METODE BAYAR','Bayar Via','COA CODE','NO GIRO','JOURNAL DATE','DISC','ADM BANK','PENERIMAAN LAIN2','BIAYA KIRIM']], null, 'A1');
        // Contoh 2 baris PIUTANG (P=Invoice, N=Kwitansi, L wajib)
        $piutang->fromArray([
            [$cust1, '18/05/2025', $branch1, 8000000, 880000, 8880000, 'P', 'Cash', 'Cash', '11211', 'abc 12345', '19/05/2025', 1000, 2500, 35000, 5000],
            [$cust2, '22/06/2025', $branch2, 3000000, 0, 3000000, 'N', 'Bank', 'Bank Transfer', '11211', '', '23/06/2025', 0, 0, 0, 0],
        ], null, 'A2');

        $petunjuk = $ss->createSheet();
        $petunjuk->setTitle('petunjuk');
        $petunjuk->fromArray(array_map(fn ($t) => [$t], [
            'PETUNJUK TEMPLATE IMPORT PAYMENT',
            '1. Isi data pada sheet "HUTANG" dan/atau "PIUTANG" mulai baris 2. (File contoh: format-hutang-piutang-v2.xlsx)',
            '2. Jangan mengubah judul kolom maupun nama sheet (HUTANG = Kode Supplier, PIUTANG = Kode Cust).',
            '3. Hanya baris dengan RO Date / Invoice Date sebelum tahun 2026 yang diproses.',
            '4. Format tanggal DD/MM/YYYY (contoh: 25/05/2025). Kolom JOURNAL DATE (HUTANG S, PIUTANG L) wajib diisi. Dilarang formula.',
            '5. Kolom PPN (HUTANG F/I) dan DPP FOB boleh 0, tetapi tidak boleh kosong/teks; kolom T-X (HUTANG) & M-P (PIUTANG) jika diisi harus numerik.',
            '6. Kode Supplier/Customer, INT Branch, Currency (Rupiah/Dollar), COA, Metode Bayar (Cash/Bank/Advance Payment) wajib terdaftar & aktif. Metode tidak case-sensitive.',
            '7. Sheet HUTANG: Journal Type = P/N (P=PPN, N=NON), Branch = INT Branch, Kode Transak (Q) sama → Journal Date (S) harus sama.',
            '8. Sheet PIUTANG: JOURNAL TYPE = P (Invoice) / N (Kwitansi), Branch = INT BRANCH, M-P DISC/ADM/PENERIMAAN/BIAYA jika kosong akan warisi dari baris atas.',
            '9. Simpan sebagai .xlsx lalu unggah kembali di halaman Import Payment.',
            '10. Sheet HUTANG sudah berisi 3 baris contoh & PIUTANG 2 baris contoh — hapus/timpa dengan data Anda. Contoh memakai kode aktif dari DB.',
            '11. Jika Kode Supplier/Customer kosong di bawah baris berisi kode, akan warisi kode di atasnya (berantai).',
        ]), null, 'A1');

        foreach ([$hutang, $piutang] as $ws) {
            $kolom = $ws->getHighestColumn();
            $ws->getStyle("A1:{$kolom}1")->getFont()->setBold(true);
            $ws->getStyle("A1:{$kolom}1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDEBF7');
            foreach (range('A', $kolom) as $c) { $ws->getColumnDimension($c)->setAutoSize(true); }
            // Style baris contoh: kuning muda + italic + border tipis
            $lastRow = $ws->getHighestRow();
            if ($lastRow > 1) {
                $ws->getStyle("A2:{$kolom}{$lastRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF2CC');
                $ws->getStyle("A2:{$kolom}{$lastRow}")->getFont()->setItalic(true)->setSize(10);
                $ws->getStyle("A2:{$kolom}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('BFBFBF'));
                $ws->freezePane('A2');
                $ws->setAutoFilter("A1:{$kolom}1");
            }
        }
        $petunjuk->getColumnDimension('A')->setWidth(90);

        return response()->streamDownload(function () use ($ss) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
        }, 'template-import-payment.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function process(Request $request)
    {
        $this->gate();

        $request->validate([
            'xlsx_file' => 'required|file|max:2048|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ], [
            'xlsx_file.required'  => 'Silakan pilih file Excel terlebih dahulu.',
            'xlsx_file.max'       => 'Ukuran file maksimal 2 MB.',
            'xlsx_file.mimetypes' => 'File harus berformat .xlsx',
        ]);

        $dir = $_SERVER['DOCUMENT_ROOT'] . '/upl/excel/';
        if (! is_dir($dir)) { mkdir($dir, 0775, true); }
        $nm = uniqid() . '_' . strtotime('now') . '.' . $request->file('xlsx_file')->extension();
        $request->file('xlsx_file')->move($dir, $nm);

        try {
            // Transaksi LUAR (savepoint): jika 1 baris throw atau error tak terduga di sheet, seluruh import hutang+piutang rollback.
            // Validasi Fase 1 (HutangRowValidator/PiutangRowValidator + pre-flight) sudah selesai sebelum transaksi tulis di sheet importer.
            $hasil = DB::transaction(function () use ($dir, $nm) {
                $imp = new HutangPiutangImport();
                Excel::import($imp, $dir . $nm);
                // Atomic rollback: jika ada 1 baris gagal, kedua sheet tidak commit
                $totalGagal = count($imp->hutang->gagal) + count($imp->piutang->gagal);
                if ($totalGagal > 0) {
                    $pesan = array_merge($imp->hutang->gagal, $imp->piutang->gagal);
                    throw ValidationException::withMessages(['file' => $pesan]);
                }
                return $imp;
            }); // ← commit hanya jika 0 gagal; rollback total jika ada gagal

            // Fitur #2: rakit catatan kegagalan -> berkas teks yang dapat diunduh (di LUAR transaksi, tetap terbuat meski rollback)
            $lewati = $hasil->hutang->skipped2026 + $hasil->piutang->skipped2026;
            $totalGagal = count($hasil->hutang->gagal) + count($hasil->piutang->gagal);
            $fileLaporan = FailureReport::save($hasil->hutang->gagal, $hasil->piutang->gagal, $lewati);

            $ringkas = sprintf(
                'Import selesai — RO: %d (%s s/d %s) · CTS: %d · INV/KWI: %d/%d (%s s/d %s) · Gagal: %d baris · Dilewati (>=2026): %d',
                $hasil->hutang->roCount,
                $hasil->hutang->roPertama ?? '-',
                $hasil->hutang->roTerakhir ?? '-',
                $hasil->hutang->ctsCount,
                $hasil->piutang->invCount,
                $hasil->piutang->kwiCount,
                $hasil->piutang->noPertama ?? '-',
                $hasil->piutang->noTerakhir ?? '-',
                $totalGagal,
                $lewati
            );

            if ($fileLaporan !== null) {
                $url = route('import-payment.report', ['filename' => $fileLaporan]);
                session()->flash(
                    'status',
                    $ringkas . '<br><a class="btn" href="' . $url . '">Unduh Catatan Kegagalan (.txt)</a>'
                );
            } else {
                session()->flash('status', $ringkas . '<br>Semua baris berhasil diproses, tidak ada catatan kegagalan.');
            }
        } catch (ValidationException $e) {
            session()->flash('status-error', implode('<br>', (array) ($e->errors()['file'] ?? ['Import gagal.'])));
        } finally {
            @unlink($dir . $nm);
        }

        return redirect('/import_payment');
    }
}
