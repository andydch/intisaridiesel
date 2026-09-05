@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>.select2-selection{height:38px!important;font-size:1rem;}</style>
@endsection

@section('wrapper')
<div class="page-wrapper"><div class="page-content">
@include('tx.'.$folder.'.breadcrumb')
<div class="row">
<form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST">@csrf
<input type="hidden" name="piutang_tmp_id" value="{{ $piutang->id ?? '' }}">
<div class="col-xl-12 mx-auto"><h6 class="mb-0 text-uppercase">{{ $title }}</h6><hr/>
<div class="card"><div class="card-body">
@if(session('status-error'))<div class="alert alert-danger">{{ session('status-error') }}</div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{!! implode('', $errors->all('<div>- :message</div>')) !!}</div>@endif
<div class="border p-4 rounded">
{{-- Customer* --}}
<div class="row mb-3"><label class="col-sm-3 col-form-label">Customer*</label><div class="col-sm-9">
<select class="form-select single-select" id="f_customer" onchange="reloadForm()"><option value="">Choose...</option>
@foreach($customerList as $c)<option value="{{ $c['kode'] }}" @if($fCustomer==$c['kode']) selected @endif>{{ $c['nama'] }} ({{ $c['kode'] }})</option>@endforeach
</select></div></div>
{{-- NPWP* --}}
<div class="row mb-3"><label class="col-sm-3 col-form-label">NPWP*</label><div class="col-sm-9">
<select class="form-select single-select" id="f_npwp" onchange="reloadForm()"><option value="">Choose...</option>
<option value="P" @if($fNpwp=='P') selected @endif>P - PPN</option><option value="N" @if($fNpwp=='N') selected @endif>N - Non PPN</option>
</select><small class="text-muted">P=PPN, N=Non PPN</small></div></div>
{{-- Journal Date* --}}
<div class="row mb-3"><label class="col-sm-3 col-form-label">Journal Date*</label><div class="col-sm-9">
<select class="form-select single-select" id="f_journal_date" onchange="reloadForm()" @if(!$fCustomer || !$fNpwp) disabled @endif><option value="">Choose...</option>
@foreach($journalDates as $d)<option value="{{ $d }}" @if($fJournalDate==$d) selected @endif>{{ $d ? date_format(date_create($d),'d/m/Y') : $d }}</option>@endforeach
</select></div></div>
@if($piutang)
  @php $metIdx=array_search((string)$piutang->metode_bayar_id, array_map('strval',$payment_mode_id), true); $metName=$metIdx!==false?$payment_mode_string[$metIdx]:$piutang->metode_bayar_id; @endphp
  <div class="row mb-3"><label class="col-sm-3 col-form-label">Metode Pembayaran</label><div class="col-sm-9"><label class="col-form-label">{{ $metName }}</label></div></div>
  <div class="row mb-3"><label class="col-sm-3 col-form-label">Pembayaran Via</label><div class="col-sm-9"><label class="col-form-label">{{ $bayarVia->title_ind ?? '-' }}</label></div></div>
  <div class="row mb-3"><label class="col-sm-3 col-form-label">No Rekening</label><div class="col-sm-9"><label class="col-form-label">{{ $coa->coa_name ?? '-' }} @if($coa) ({{ $coa->coa_code_complete }}) @endif</label></div></div>
  <div class="row mb-3"><label class="col-sm-3 col-form-label">Transaction / Giro No</label><div class="col-sm-9"><label class="col-form-label">{{ $piutang->no_giro ?: '-' }}</label></div></div>
  <div class="row mb-3"><label class="col-sm-3 col-form-label">Total Pembayaran (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($piutang->total,0,'.',',') }}</label></div></div>
@endif
@if($piutang)
<hr/>
<table class="table table-bordered mb-0"><thead><tr>
<th style="width:3%;text-align:center;">#</th><th>Invoice No</th><th>Date</th><th>Billing No</th><th>Description</th><th style="text-align:right;">Total ({{ $qCurrency->string_val ?? 'Rp' }})</th>
</tr></thead><tbody>
@foreach($invList as $i=>$inv)
<tr><th style="text-align:right;">{{ $i+1 }}.</th>
<td>{{ $inv->invoice_no ?? $inv->kwitansi_no ?? '-' }}</td>
<td>{{ isset($inv->invoice_date) && $inv->invoice_date ? date_format(date_create($inv->invoice_date),'d/m/Y') : (isset($inv->created_at) ? date_format(date_create($inv->created_at),'d/m/Y'):'-') }}</td>
<td>{{ $inv->id }}</td>
<td>{{ $inv->remark ?? $inv->description ?? '-' }}</td>
<td style="text-align:right;">{{ number_format($piutang->total,0,'.',',') }}</td></tr>
@endforeach
@if($invList->count()==0)<tr><td colspan="6" style="text-align:center;"><em>Tidak ada Billing</em></td></tr>@endif
</tbody><tfoot>
@php $isPpn=($fNpwp=='P'); $vatPct=(float)($qVat->numeric_val??0); $sumBase=$sum; $sumVat=0; if($isPpn && $sum>0){ $sumBase=round($piutang->total/(1+$vatPct/100),2); $sumVat=$piutang->total-$sumBase; } $grand=$sumBase+$sumVat - (float)$piutang->discount + (float)$piutang->admin_bank + (float)$piutang->biaya_kirim + (float)$piutang->penerimaan_lain; @endphp
<tr><td colspan="5" style="text-align:right;">Total</td><td style="text-align:right;">{{ number_format($sumBase,0,'.',',') }}</td></tr>
@if($isPpn)<tr><td colspan="5" style="text-align:right;">VAT ({{ $vatPct }}%)</td><td style="text-align:right;">{{ number_format($sumVat,0,'.',',') }}</td></tr>@endif
<tr><td colspan="5" style="text-align:right;">Total Biaya Lainnya</td><td style="text-align:right;">{{ number_format((float)$piutang->biaya_kirim,0,'.',',') }}</td></tr>
<tr><td colspan="5" style="text-align:right;"><strong>Grand Total</strong></td><td style="text-align:right;"><strong>{{ number_format($grand,0,'.',',') }}</strong></td></tr>
</tfoot></table>
<div style="margin-top:15px;">
<div class="row mb-3"><label class="col-sm-3 col-form-label">Diskon (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($piutang->discount,0,'.',',') }}</label></div></div>
<div class="row mb-3"><label class="col-sm-3 col-form-label">Admin Bank (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($piutang->admin_bank,0,'.',',') }}</label></div></div>
<div class="row mb-3"><label class="col-sm-3 col-form-label">Penerimaan Lainnya (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($piutang->penerimaan_lain,0,'.',',') }}</label></div></div>
<div class="row mb-3"><label class="col-sm-3 col-form-label">Biaya Kirim (Rp)</label><div class="col-sm-9"><label class="col-form-label">{{ number_format($piutang->biaya_kirim,0,'.',',') }}</label></div></div>
</div>
@endif
<div class="col-12" style="margin-top:15px;"><button type="submit" class="btn btn-primary px-5" @if(!$piutang) disabled @endif>Save</button> <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt') }}" class="btn btn-secondary px-5">Cancel</a></div>
</div></div></div></div>
</form></div></div></div>
@endsection
@push('scripts')
<script>
function reloadForm(){
  const c=document.getElementById('f_customer').value, n=document.getElementById('f_npwp').value, j=document.getElementById('f_journal_date').value;
  let u='{{ url(ENV("TRANSACTION_FOLDER_NAME")."/payment-receipt-temp") }}?customer='+encodeURIComponent(c)+'&npwp='+n;
  if(j) u+='&journal_date='+encodeURIComponent(j);
  window.location=u;
}
document.addEventListener('DOMContentLoaded',function(){ if(window.$ && $('.single-select').select2) $('.single-select').select2({theme:'bootstrap4'}); document.getElementById('submit-form').addEventListener('submit',function(e){ if(!confirm("Data akan disimpan ke database. Pastikan data sudah benar.\nLanjutkan?")){ e.preventDefault(); return false; } const b=this.querySelector('button[type=submit]'); if(b){b.disabled=true;b.innerText='Saving...';}})});
</script>
@endpush
