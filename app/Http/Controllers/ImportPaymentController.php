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
    /** Gate akses: hanya andydch@koidigital.co.id */
    private function gate(): void
    {
        abort_unless(Auth::check() && Auth::user()->email === 'andydch@koidigital.co.id', 403, 'Akses ditolak.');
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
     * Unduh template Excel kosong untuk diisi data lalu di-import kembali.
     * Judul kolom & nama sheet = kontrak importer (pelindung anti salah-posisi).
     */
    public function template()
    {
        $this->gate();

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $hutang = $ss->getActiveSheet();
        $hutang->setTitle('kartu-hutang');
        $hutang->fromArray([['No.','Supplier Code','RO Date','Jurnal Type','Currency','DPP','PPN',
            'DPP(+PPN)','DPP FOB','PPN FOB','DPP(+PPN) FOB','Branch','Invoice No','CTS COA Code',
            'CTS Payment Plan Date','Kode Transaksi']], null, 'A1');

        $piutang = $ss->createSheet();
        $piutang->setTitle('kartu-piutang');
        $piutang->fromArray([['No.','Customer Code','Invoice Date','Branch','DPP','PPN','TOTAL',
            'PPN/NON PPN','Metode Pembayaran','COA Code']], null, 'A1');

        $petunjuk = $ss->createSheet();
        $petunjuk->setTitle('petunjuk');
        $petunjuk->fromArray(array_map(fn ($t) => [$t], [
            'PETUNJUK TEMPLATE IMPORT PAYMENT',
            '1. Isi data pada sheet "kartu-hutang" dan/atau "kartu-piutang" mulai baris 2.',
            '2. Jangan mengubah judul kolom maupun nama sheet.',
            '3. Hanya baris dengan RO Date / Invoice Date sebelum tahun 2026 yang diproses.',
            '4. Format tanggal DD/MM/YYYY (contoh: 25/05/2025). Dilarang menggunakan formula.',
            '5. Kolom PPN boleh diisi 0, tetapi tidak boleh kosong atau teks.',
            '6. Kode Supplier/Customer, Branch, Currency, dan COA wajib terdaftar & aktif.',
            '7. Sheet kartu-hutang: Jurnal Type = P/N, Currency = RP/USD.',
            '8. Sheet kartu-piutang: PPN/NON PPN = P (dibuat Invoice) / N (dibuat Kwitansi).',
            '9. Simpan sebagai .xlsx lalu unggah kembali di halaman Import Payment.',
        ]), null, 'A1');

        foreach ([$hutang, $piutang] as $ws) {
            $kolom = $ws->getHighestColumn();
            $ws->getStyle("A1:{$kolom}1")->getFont()->setBold(true);
            $ws->getStyle("A1:{$kolom}1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDEBF7');
            foreach (range('A', $kolom) as $c) { $ws->getColumnDimension($c)->setAutoSize(true); }
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
            $hasil = DB::transaction(function () use ($dir, $nm) {
                $imp = new HutangPiutangImport();
                Excel::import($imp, $dir . $nm);
                return $imp;
            });

            // Fitur #2: rakit catatan kegagalan -> berkas teks yang dapat diunduh
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
