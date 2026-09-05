@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection { height: 38px !important; font-size: 1rem; }
</style>
@endsection

@section('wrapper')
<div class="page-wrapper">
    <div class="page-content">
        @include('tx.'.$folder.'.breadcrumb')
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST">
                @csrf
                <input type="hidden" name="hutang_tmp_id" value="{{ $hutang->id ?? '' }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if(session('status-error'))
                                <div class="alert alert-danger">{{ session('status-error') }}</div>
                            @endif
                            @if(session('status'))
                                <div class="alert alert-success">{{ session('status') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">{{ implode('', $errors->all('<div>- :message</div>')) }}</div>
                            @endif
                            <div class="border p-4 rounded">
                                {{-- 1. Supplier* --}}
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Supplier*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select" id="f_supplier" onchange="reloadForm()">
                                            <option value="">Choose...</option>
                                            @foreach ($supplierList as $s)
                                                <option value="{{ $s['kode'] }}" @if($fSupplier==$s['kode']) selected @endif>{{ $s['nama'] }} ({{ $s['kode'] }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- 2. NPWP* --}}
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">NPWP*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select" id="f_npwp" onchange="reloadForm()">
                                            <option value="">Choose...</option>
                                            <option value="P" @if($fNpwp=='P') selected @endif>P - PPN</option>
                                            <option value="N" @if($fNpwp=='N') selected @endif>N - Non PPN</option>
                                        </select>
                                        <small class="text-muted">P = PPN, N = Non PPN</small>
                                    </div>
                                </div>
                                {{-- 3. No Tagihan Supplier* --}}
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">No Tagihan Supplier*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select" id="f_tagihan" onchange="reloadForm()" @if(!$fSupplier || !$fNpwp) disabled @endif>
                                            <option value="">Choose...</option>
                                            @foreach ($tagihans as $t)
                                                <option value="{{ $t['id'] }}" @if($fTagihan==$t['id']) selected @endif>{{ $t['no'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if ($hutang)
                                    @php
                                        $metodeIdx = array_search((string)$hutang->metode_bayar_id, array_map('strval',$payment_mode_id), true);
                                        $metodeName = $metodeIdx !== false ? $payment_mode_string[$metodeIdx] : $hutang->metode_bayar_id;
                                    @endphp
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Metode Pembayaran</label><div class="col-sm-9"><label class="col-form-label">{{ $metodeName }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Pembayaran Via</label><div class="col-sm-9"><label class="col-form-label">{{ $bayarVia->title_ind ?? '-' }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">No Rekening</label><div class="col-sm-9"><label class="col-form-label">{{ $coa->coa_name ?? '-' }} @if($coa) ({{ $coa->coa_code_complete }}) @endif</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Transaction / Giro No</label><div class="col-sm-9"><label class="col-form-label">{{ $hutang->no_giro ?: '-' }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Total Pembayaran (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->total,0,'.',',') }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Journal Date</label><div class="col-sm-9"><label class="col-form-label">{{ $hutang->jurnal_date ? date_format(date_create($hutang->jurnal_date),'d/m/Y') : '-' }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Journal Type</label><div class="col-sm-9"><label class="col-form-label">{{ $hutang->journal_type=='P'?'P - PPN':'N - Non PPN' }}</label></div></div>
                                @endif

                                @if ($hutang)
                                <hr/>
                                <table class="table table-bordered mb-0">
                                    <thead><tr>
                                        <th style="width:3%;text-align:center;">#</th>
                                        <th style="width:15%;">Invoice No</th>
                                        <th style="width:12%;">Date</th>
                                        <th style="width:17%;">RO No</th>
                                        <th style="width:25%;">Description</th>
                                        <th style="width:18%;text-align:right;">Total ({{ $qCurrency->string_val ?? 'Rp' }})</th>
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($roList as $i=>$ro)
                                        <tr>
                                            <th style="text-align:right;">{{ $i+1 }}.</th>
                                            <td><label class="col-form-label">{{ $ro->invoice_no }}</label></td>
                                            <td><label class="col-form-label">{{ $ro->receipt_date ? date_format(date_create($ro->receipt_date),'d/m/Y') : '-' }}</label></td>
                                            <td><label class="col-form-label">{{ $ro->receipt_no }}</label></td>
                                            <td><label class="col-form-label">{{ $ro->description }}</label></td>
                                            <td style="text-align:right;"><label class="col-form-label">{{ number_format($ro->total_price_per_ro,0,'.',',') }}</label></td>
                                        </tr>
                                        @endforeach
                                        @if($roList->count()==0)
                                        <tr><td colspan="6" style="text-align:center;"><em>Tidak ada detail RO aktif</em></td></tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        @php
                                            $isPpn = ($fNpwp=='P');
                                            $vatPct = (float)($qVat->numeric_val ?? 0);
                                            $sumBase = $sumRo;
                                            $sumVat  = 0;
                                            if ($isPpn && $sumRo > 0) {
                                                $sumBase = round($hutang->total / (1 + $vatPct/100), 2);
                                                $sumVat  = $hutang->total - $sumBase;
                                            } elseif ($hutang->total != $sumRo) {
                                                $sumBase = $hutang->total;
                                            }
                                            $sumBiaya = (float)$hutang->admin_bank + (float)$hutang->biaya_asuransi
                                                        + (float)$hutang->biaya_kirim + (float)$hutang->biaya_lain
                                                        - (float)$hutang->discount;
                                            $grand = $sumBase + $sumVat + $sumBiaya;
                                        @endphp
                                        <tr><td style="text-align:right;" colspan="5">Total</td><td style="text-align:right;"><label>{{ number_format($sumBase,0,'.',',') }}</label></td></tr>
                                        @if ($isPpn)
                                        <tr><td style="text-align:right;" colspan="5">VAT ({{ $vatPct }}%)</td><td style="text-align:right;"><label>{{ number_format($sumVat,0,'.',',') }}</label></td></tr>
                                        @endif
                                        <tr><td style="text-align:right;" colspan="5">Total Biaya Lain-lain</td><td style="text-align:right;"><label>{{ number_format($sumBiaya,0,'.',',') }}</label></td></tr>
                                        <tr><td style="text-align:right;" colspan="5"><strong>Grand Total</strong></td><td style="text-align:right;"><label><strong>{{ number_format($grand,0,'.',',') }}</strong></label></td></tr>
                                    </tfoot>
                                </table>

                                <div style="margin-top:15px;">
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Admin Bank (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->admin_bank,0,'.',',') }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Biaya Asuransi (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->biaya_asuransi,0,'.',',') }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Biaya Kirim (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->biaya_kirim,0,'.',',') }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Biaya Lainnya (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->biaya_lain,0,'.',',') }}</label></div></div>
                                    <div class="row mb-3"><label class="col-sm-3 col-form-label">Diskon Pembelian (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($hutang->discount,0,'.',',') }}</label></div></div>
                                </div>
                                @endif

                                <div class="col-12" style="margin-top:15px;">
                                    <button type="submit" class="btn btn-primary px-5" @if(!$hutang) disabled @endif>Save</button>
                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher') }}" class="btn btn-secondary px-5">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reloadForm(){
  const s = document.getElementById('f_supplier').value;
  const n = document.getElementById('f_npwp').value;
  const t = document.getElementById('f_tagihan').value;
  let u = '{{ url(ENV("TRANSACTION_FOLDER_NAME")."/payment-voucher-temp") }}?supplier='+encodeURIComponent(s)+'&npwp='+n;
  if (t) u += '&tagihan='+t;
  window.location = u;
}
document.addEventListener('DOMContentLoaded', function(){
  if(window.$ && $('.single-select').select2){ $('.single-select').select2({theme:'bootstrap4'}); }
  document.getElementById('submit-form').addEventListener('submit', function(e){
    if(!confirm("Data akan disimpan ke database. Pastikan data sudah benar.\nLanjutkan?")){
      e.preventDefault();
      return false;
    }
    const btn = this.querySelector('button[type=submit]');
    if(btn){ btn.disabled=true; btn.innerText='Saving...'; }
  });
});
</script>
@endpush
