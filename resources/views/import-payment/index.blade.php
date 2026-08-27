<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Import Payment (Supplier &amp; Customer)</title>
<style>
 body{font-family:system-ui,Arial,sans-serif;background:#f4f6f9;margin:0;padding:24px}
 .card{max-width:720px;margin:auto;background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
 h1{font-size:20px;margin-top:0;color:#233}
 ol{color:#556;line-height:1.7;font-size:14px}
 .alert{padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px}
 .alert.ok{background:#e6f7ec;color:#137333}
 .alert.err{background:#fdecea;color:#b3261e;white-space:pre-line}
 .btn{background:#2563eb;color:#fff;border:0;border-radius:6px;padding:10px 18px;cursor:pointer;font-family:inherit;font-size:14px;line-height:1.5;box-sizing:border-box}
 .btn:hover{background:#1e50c8}
 .btn.gray{background:#64748b;text-decoration:none;display:inline-block}
 .btn.gray:hover{background:#4b5563}
 .alert .btn{display:inline-block;margin-top:10px;text-decoration:none;font-size:13px;font-weight:600}
 a.btn-link{display:inline-block;margin-top:8px;padding:6px 12px;border:1px solid #2563eb;color:#2563eb;border-radius:6px;text-decoration:none;font-size:13px}
</style>
</head>
<body>
<div class="card">
  <h1>Import Payment (Supplier &amp; Customer)</h1>

  @if (session('status'))
    <div class="alert ok">{!! session('status') !!}</div>
  @endif

  @if (session('status-error'))
    <div class="alert err">IMPORT GAGAL:<br>{!! session('status-error') !!}</div>
  @endif

  <ol>
    <li>Gunakan file <code>format-hutang-piutang.xlsx</code> dengan worksheet
        <strong>kartu-hutang</strong> (kolom M = Invoice No) dan <strong>kartu-piutang</strong>.</li>
    <li>Hanya data dengan RO Date / Invoice Date <strong>sebelum tahun 2026</strong> yang diproses;
        baris lainnya dilewati &amp; dilaporkan.</li>
    <li>Kode Supplier/Customer wajib <strong>ada &amp; aktif</strong>; baris yang tidak bisa diproses
        dicatat dalam berkas teks yang dapat diunduh setelah import.</li>
    <li>Maksimal ukuran file 2 MB (.xlsx).</li>
  </ol>

  <form method="POST" action="{{ url('/import_payment') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="xlsx_file" accept=".xlsx" required
           style="border:1px solid #cbd5e1;border-radius:6px;padding:8px;width:100%;box-sizing:border-box;margin-bottom:16px">
    <div style="display:flex;gap:10px;align-items:center">
      <button class="btn" type="submit">Proses Import</button>
      <a class="btn gray" href="{{ url('/import_payment/template') }}">Download Template</a>
    </div>
  </form>
</div>
</body>
</html>
