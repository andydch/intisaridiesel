@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}
<link href="{{ asset('assets/css/fonts.css') }}" rel="stylesheet">

<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            @if(session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                    <div class="col-sm-9">
                                        <input type="hidden" id="supplier_type_id" name="supplier_type_id" value="{{ old('supplier_type_id')?old('supplier_type_id'):0 }}">
                                        <select class="form-select single-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierId = (old('supplier_id')?old('supplier_id'):0);
                                            @endphp
                                            @foreach ($suppliers as $p)
                                                <option @if($supplierId==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">
                                                    {{ $p->supplier_code.' - '.($p->entity_type?$p->entity_type->title_ind.' ':' ').$p->name }}                                                    
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- @php
                                    $bankInfo = '';
                                    $query = \App\Models\Mst_supplier_bank_information::select(
                                        'bank_name',
                                        'bank_address',
                                        'account_name',
                                        'account_no',
                                    )
                                    ->where([
                                        'supplier_id'=>old('supplier_id'),
                                    ])
                                    ->orderBy('bank_name','ASC')
                                    ->get();
                                @endphp --}}
                                {{-- <div id="supplier-bank-info" class="row mb-3" 
                                    @if(old('supplier_id') && count($query)>0){!! 'style="display: block ruby;"' !!}@else{!! 'style="display: none;"' !!}@endif>
                                    @if ($query)
                                        <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                        <div class="col-sm-9">
                                            <span id="supplier-bank-info-detail">
                                                @foreach ($query as $q)
                                                    @php
                                                        $bankInfo .= '- '.$q->bank_name.', no rek '.$q->account_no.', an. '.$q->account_name.'<br/>';
                                                    @endphp
                                                @endforeach
                                                {!! $bankInfo !!}
                                            </span>
                                        </div>
                                    @endif
                                </div> --}}
                                <div class="row mb-3">
                                    <label for="payment_type_id" class="col-sm-3 col-form-label">NPWP*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('payment_type_id') is-invalid @enderror" id="payment_type_id" name="payment_type_id">
                                            <option value="">Choose...</option>
                                            @php
                                                $payment_type_id = (old('payment_type_id')?old('payment_type_id'):0);
                                            @endphp
                                            @foreach ($payment_type as $t)
                                                <option @if($payment_type_id==$t){{ 'selected' }}@endif value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="payment_mode_id" class="col-sm-3 col-form-label">Metode Pembayaran*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('payment_mode_id') is-invalid @enderror"
                                            id="payment_mode_id" name="payment_mode_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $paymentModeId = (old('payment_mode_id')?old('payment_mode_id'):0);
                                            @endphp
                                            @for ($i=0;$i<count($payment_mode_string);$i++)
                                                <option @if($paymentModeId==($i+1)){{ 'selected' }}@endif
                                                    value="{{ $i+1 }}">{{ $payment_mode_string[$i] }}</option>
                                            @endfor
                                        </select>
                                        @error('payment_mode_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="ref_id" class="col-sm-3 col-form-label">Pembayaran Via*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('ref_id') is-invalid @enderror" id="ref_id" name="ref_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $prefId = (old('ref_id')?old('ref_id'):0);
                                                $paymentRef = \App\Models\Mst_global::select(
                                                    'id',
                                                    'title_ind',
                                                    'title_eng',
                                                    'slug',
                                                )
                                                ->when($prefId!=1 && $prefId!=2, function($q) use($prefId){
                                                    $q->where('id','=',$prefId);
                                                })
                                                ->when($prefId==1, function($q){
                                                    $q->whereIn('id',[51]);
                                                })
                                                ->when($prefId==2, function($q){
                                                    $q->whereIn('id',[49,50,63]);
                                                })
                                                ->where([
                                                    'data_cat'=>'payment-ref',
                                                    'active'=>'Y',
                                                ])
                                                ->orderBy('title_ind','ASC')
                                                ->get();
                                            @endphp
                                            @foreach ($paymentRef as $pr)
                                                <option @if($prefId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $pr->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('ref_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="tagihan_supplier_id" class="col-sm-3 col-form-label">No Tagihan Supplier*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('tagihan_supplier_id') is-invalid @enderror" id="tagihan_supplier_id" name="tagihan_supplier_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $tagihan_supplier_id = (old('tagihan_supplier_id')?old('tagihan_supplier_id'):0);
                                                $ref_id = (old('ref_id')?old('ref_id'):0);
                                                $payment_mode_id = (old('payment_mode_id')?old('payment_mode_id'):0);
                                                $is_vat = 'X';
                                                switch (old('payment_type_id')) {
                                                    case 'P':
                                                        $is_vat = 'Y';
                                                        break;
                                                    case 'N':
                                                        $is_vat = 'N';
                                                        break;
                                                    default:
                                                        $is_vat = 'X';
                                                }
                                            @endphp
                                            @php
                                                $queryTagihanSupplier = \App\Models\Tx_tagihan_supplier::select(
                                                    'id',
                                                    'tagihan_supplier_no',
                                                )
                                                ->when($payment_mode_id==1, function($q) {
                                                    $q->whereIn('bank_id', function($q1){
                                                        $q1->select('id')
                                                        ->from('mst_coas')
                                                        ->where('coa_code_complete', 'LIKE', '111%')
                                                        ->where('active', '=', 'Y');
                                                    });
                                                })
                                                ->when($payment_mode_id==2, function($q) {
                                                    $q->whereIn('bank_id', function($q1){
                                                        $q1->select('id')
                                                        ->from('mst_coas')
                                                        ->where('coa_code_complete', 'LIKE', '112%')
                                                        ->where('active', '=', 'Y');
                                                    });
                                                })
                                                ->when($payment_mode_id==3, function($q) {
                                                    $q->whereIn('bank_id', function($q1){
                                                        $q1->select('id')
                                                        ->from('mst_coas')
                                                        ->where('coa_code_complete', 'LIKE', '116%')
                                                        ->where('active', '=', 'Y');
                                                    });
                                                })
                                                ->whereNotIn('id', function($q2) {
                                                    $q2->select('tagihan_supplier_id')
                                                    ->from('tx_payment_vouchers')
                                                    ->where('supplier_id', old('supplier_id'))
                                                    ->whereRaw('tagihan_supplier_id IS NOT NULL')
                                                    ->where('is_full_payment', 'Y')
                                                    ->where('active', 'Y');
                                                })
                                                ->where([
                                                    'supplier_id'=>old('supplier_id'),
                                                    'is_vat' => $is_vat,
                                                    'active'=>'Y',
                                                ])
                                                ->orderBy('id','DESC')
                                                ->get();
                                            @endphp
                                            @foreach ($queryTagihanSupplier as $ts)
                                                <option @if($tagihan_supplier_id==$ts->id){{ 'selected' }}@endif value="{{ $ts->id }}">{{ $ts->tagihan_supplier_no }}</option>
                                            @endforeach
                                        </select>
                                        @error('tagihan_supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="coa_id" class="col-sm-3 col-form-label">No Rekening*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('coa_id') is-invalid @enderror" id="coa_id" name="coa_id">
                                            <option value="">Choose...</option>
                                            @php
                                                $coaId = (old('coa_id')?old('coa_id'):0);
                                                $payment_group = ($payment_type_id=='P'?8:13);
                                                $supplier_id = $supplierId;
                                                $userLogin = \App\Models\Userdetail::where('user_id','=',Auth::user()->id)->first();

                                                $queryCoa = \App\Models\Mst_coa::select(
                                                    'id',
                                                    'coa_name'
                                                )
                                                ->when($payment_mode_id==1, function($q) use($payment_group,$supplier_id,$userLogin){
                                                    $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_details')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [1])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'cash\'');
                                                    })
                                                    ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_detail_exts')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [1])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'cash\'');
                                                    });
                                                })
                                                ->when($payment_mode_id==2, function($q) use($payment_group,$supplier_id,$userLogin){
                                                    $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_details')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [2])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'bank\'');
                                                    })
                                                    ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_detail_exts')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [2])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'bank\'');
                                                    });
                                                })
                                                ->when($payment_mode_id==3, function($q) use($payment_group,$supplier_id,$userLogin){
                                                    $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_details')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [3])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'advance payment\'');
                                                    })
                                                    ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_detail_exts')
                                                        ->where('auto_journal_id', '=', $payment_group)
                                                        ->whereIn('method_id', [3])
                                                        ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                                                            $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                                                        })
                                                        ->where('active', '=', 'Y')
                                                        ->whereRaw('LOWER(`desc`)=\'advance payment\'');
                                                    });
                                                })
                                                ->where([
                                                    // 'coa_level' => 5,
                                                    // 'is_master_coa' => 'N',
                                                    'active' => 'Y'
                                                ])
                                                ->orderBy('coa_name','ASC')
                                                ->get();
                                            @endphp
                                            @foreach ($queryCoa as $coa)
                                                <option @if($coaId==$coa->id){{ 'selected' }}@endif value="{{ $coa->id }}">{{ $coa->coa_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('coa_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="reference_no" class="col-sm-3 col-form-label">Transaction / Giro No</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('reference_no') is-invalid @enderror"
                                            maxlength="255" id="reference_no" name="reference_no" placeholder="Reference No"
                                            value="@if(old('reference_no')){{ old('reference_no') }}@endif">
                                        @error('reference_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="reference_date" class="col-sm-3 col-form-label">Transaction / Giro Date</label>
                                    <div class="col-sm-9">
                                        <input readonly type="text" class="form-control @error('reference_date') is-invalid @enderror"
                                            maxlength="10" id="reference_date" name="reference_date" placeholder="Reference Date"
                                            value="@if(old('reference_date')){{ old('reference_date') }}@endif">
                                        @error('reference_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="total_payment" class="col-sm-3 col-form-label">Total Pembayaran ({{ $qCurrency->string_val }})*</label>
                                    <div class="col-sm-9">
                                        <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('total_payment') is-invalid @enderror"
                                            maxlength="50" id="total_payment" name="total_payment" placeholder="Total"
                                            value="@if(old('total_payment')){{ old('total_payment') }}@endif">
                                        @error('total_payment')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="payment_date" class="col-sm-3 col-form-label">Journal Date*</label>
                                    <div class="col-sm-9">
                                        <input readonly type="text" class="form-control @error('payment_date') is-invalid @enderror"
                                            maxlength="10" id="payment_date" name="payment_date" placeholder="Date"
                                            value="@if(old('payment_date')){{ old('payment_date') }}@endif">
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="journal_type_id" class="col-sm-3 col-form-label">Journal Type</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('journal_type_id') is-invalid @enderror" id="journal_type_id" name="journal_type_id">
                                            <option value="">Choose...</option>
                                            @php
                                                $journal_type_id = (old('journal_type_id')?old('journal_type_id'):0);
                                            @endphp
                                            @foreach ($payment_type as $t)
                                                <option @if($journal_type_id==$t){{ 'selected' }}@endif value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        @error('journal_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                    <div class="col-sm-9">
                                        <textarea name="remark" id="remark" rows="3" maxlength="2000" style="width: 100%;"
                                            class="form-control @error('remark') is-invalid @enderror">@if(old('remark')){{ old('remark') }}@endif</textarea>
                                        @error('remark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3 mt-5">
                                    <label for="remark" class="col-sm-12 col-form-label" style="color:red;font-style:italic;">*Please wait until all data can be displayed properly!</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                                $totTerbayar = 0;
                                $disabled = '';
                                $readonly = '';
                                if ($tagihan_supplier_id!=0 && $tagihan_supplier_id!='#') {
                                    // $disabled = 'disabled="disabled"';
                                    $readonly = 'readonly';
                                }
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 12%;">Invoice No</th>
                                        <th scope="col" style="width: 10%;">Date</th>
                                        <th scope="col" style="width: 15%;">RO No - RE No</th>
                                        <th scope="col" style="width: 12%;">Description</th>
                                        <th scope="col" style="width: 13%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 13%;">Terbayar ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 13%;">Sisa ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 3%;text-align:center;">&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $ro_vat = 0;
                                        $ro_vat_total = 0;
                                    @endphp
                                    @for ($i = 0; $i < $totRow; $i++)
                                        @if(old('invoice_no_'.$i))
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label for="" class="col-form-label">{{ $i+1 }}.</label>
                                                </th>
                                                <td>
                                                    <select onchange="dispTotPrice(this.value, {{ $i }});"
                                                        class="form-select single-select @error('invoice_no_'.$i) is-invalid @enderror" id="invoice_no_{{ $i }}" name="invoice_no_{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $roId = old('invoice_no_'.$i)?old('invoice_no_'.$i) : 0;
                                                        @endphp
                                                        @foreach ($receiptOrders as $ro)
                                                            <option @if($roId==$ro->id){{ 'selected' }}@else{{ $disabled }}@endif value="{{ $ro->id }}">{{ $ro->invoice_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('invoice_no_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                @php
                                                    $RoNo = '';
                                                    $receipt_date_01 = '';
                                                    $returNo = '';
                                                    $totEveryReturBeforeVat = '';
                                                    $exchange_rate = 1;
                                                    $exch_rate_for_vat = 1;
                                                    $receipt_id = old('receipt_id_'.$i)?old('receipt_id_'.$i) : 0;
                                                    $qRO = \App\Models\Tx_receipt_order::where('id','=',$receipt_id)
                                                    ->first();
                                                    if($qRO){
                                                        $RoNo = $qRO->receipt_no;
                                                        $receipt_date_01 = date_format(date_create($qRO->receipt_date),"d/m/Y");
                                                        $exchange_rate = (!is_null($qRO->exchange_rate) && $qRO->exchange_rate>0?$qRO->exchange_rate:1);
                                                        $exch_rate_for_vat = (!is_null($qRO->exc_rate_for_vat) && $qRO->exc_rate_for_vat>0?$qRO->exc_rate_for_vat:1);
                                                        $ro_vat = $qRO->vat_val;
                                                    }
                                                    $qReturs = \App\Models\Tx_purchase_retur::where('receipt_order_id','=',$receipt_id)
                                                    ->whereRaw('approved_by IS NOT NULL')
                                                    ->get();
                                                @endphp
                                                @foreach ($qReturs as $qR)
                                                    @php
                                                        $returNo .= '<br/>'.$qR->purchase_retur_no;
                                                        $totEveryReturBeforeVat .= '('.number_format(($qR->total_before_vat),0,".",",").')<br/>';
                                                        // $totEveryReturBeforeVat .= '('.number_format(($qR->total_before_vat*$exchange_rate),0,".",",").')<br/>';
                                                    @endphp
                                                @endforeach
                                                <td>
                                                    <label id="ro_date_{{ $i }}" class="col-form-label">{{ $receipt_date_01 }}</label>
                                                </td>
                                                <td>
                                                    <input type="hidden" id="receipt_id_{{ $i }}" name="receipt_id_{{ $i }}"
                                                        value="@if(old('receipt_id_'.$i)){{ old('receipt_id_'.$i) }}@endif" />
                                                    <label id="ro_and_retur_{{ $i }}" class="col-form-label">{!! $RoNo.$returNo !!}</label>
                                                </td>                                                
                                                <td>
                                                    <textarea class="form-control @error('desc_'.$i) is-invalid @enderror"
                                                        name="desc_{{ $i }}" id="desc_{{ $i }}" rows="3"
                                                        style="width: 100%;" {{ $readonly }}>@if(old('desc_'.$i)){{ old('desc_'.$i) }}@endif</textarea>
                                                    @error('desc_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    <input type="hidden" id="total_before_vat_ro_{{ $i }}" name="total_before_vat_ro_{{ $i }}"
                                                        value="@if(old('total_before_vat_ro_'.$i)){{ old('total_before_vat_ro_'.$i) }}@endif" />
                                                    <input type="hidden" id="total_vat_ro_{{ $i }}" name="total_vat_ro_{{ $i }}"
                                                        value="@if(old('total_vat_ro_'.$i)){{ old('total_vat_ro_'.$i) }}@endif" />
                                                    <input type="hidden" id="total_inv_before_retur_{{ $i }}" name="total_inv_before_retur_{{ $i }}"
                                                        value="@if(old('total_inv_before_retur_'.$i)){{ old('total_inv_before_retur_'.$i) }}@endif" />
                                                    <input type="hidden" id="total_inv_o_{{ $i }}" name="total_inv_o_{{ $i }}"
                                                        value="@if(old('total_inv_o_'.$i)){{ old('total_inv_o_'.$i) }}@endif" />
                                                    <input type="hidden" id="retur_val_o_{{ $i }}" name="retur_val_o_{{ $i }}"
                                                        value="@if(old('retur_val_o_'.$i)){{ old('retur_val_o_'.$i) }}@endif" />
                                                    <label id="total_inv_lbl_{{ $i }}" class="col-form-label"
                                                        style="padding-bottom:0;">@if(old('total_inv_before_retur_'.$i)){{ number_format(old('total_inv_before_retur_'.$i),0,".",",") }}@endif</label><br/>
                                                    <label id="retur_val_{{ $i }}" class="col-form-label" style="color: red;padding-top:0;">{!! $totEveryReturBeforeVat !!}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    @if (old('supplier_type_id')==10)
                                                        @php
                                                            $ro_vat_total += (((float)str_replace(",","",old('total_inv_'.$i))/(float)old('total_before_vat_ro_'.$i))*
                                                                (float)old('total_vat_ro_'.$i));
                                                        @endphp
                                                    @endif
                                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('total_inv_'.$i) is-invalid @enderror"
                                                        id="total_inv_{{ $i }}" name="total_inv_{{ $i }}" maxlength="25"
                                                        value="@if(old('total_inv_'.$i)){{ number_format(str_replace(",","",old('total_inv_'.$i)),0,".",",") }}@endif"
                                                        style="text-align: right;" />
                                                    {{-- <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('total_inv_'.$i) is-invalid @enderror"
                                                        id="total_inv_{{ $i }}" name="total_inv_{{ $i }}" maxlength="25"
                                                        value="@if(old('total_inv_'.$i)){{ number_format(str_replace(",","",old('total_inv_'.$i)),0,".",",") }}@endif"
                                                        style="text-align: right;" {{ $readonly }} /> --}}
                                                    @error('total_inv_'.$i)
                                                        <div class="invalid-feedback">{!! $message !!}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;"><label for="" class="col-form-label">0</label></td>
                                                <td style="text-align: center;">
                                                    {{-- <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}" {{ $disabled }} style="margin-top: 10px;" /> --}}
                                                </td>
                                            </tr>
                                        @endif
                                        @php
                                            if (is_numeric(str_replace(",","", old('total_inv_'.$i)))){
                                                $totTerbayar += floatval(str_replace(",", "", old('total_inv_'.$i)));
                                            }
                                        @endphp
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td style="text-align: right;" colspan="5">Total</td>
                                        <td style="text-align: right">
                                            <label for="" id="tot-terbayar">{{ number_format($totTerbayar,0,".",",") }}</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;" colspan="5">VAT</td>
                                        <td style="text-align: right">
                                            @if (old('supplier_type_id')==10)
                                                <label for="" id="vat-terbayar">{{ number_format($ro_vat_total,0,".",",") }}</label>
                                            @else
                                                <label for="" id="vat-terbayar">{{ $ro_vat==0?0:number_format(($totTerbayar*$ro_vat)/100,0,".",",") }}</label>
                                            @endif
                                        </td>
                                    </tr>
                                    <td style="text-align: right;" colspan="5">Total Biaya Lain-lain</td>
                                        <td style="text-align: right">
                                            @php
                                                $totalBiayaLain2 = (old('admin_bank')?(float)str_replace(",","",old('admin_bank')):0)+
                                                    (old('biaya_kirim')?(float)str_replace(",","",old('biaya_kirim')):0)+
                                                    (old('biaya_kirim')?(float)str_replace(",","",old('biaya_lainnya')):0)+
                                                    (old('biaya_asuransi')?(float)str_replace(",","",old('biaya_asuransi')):0)-
                                                    (old('diskon_pembelian')?(float)str_replace(",","",old('diskon_pembelian')):0);
                                            @endphp
                                            <label for="" id="biaya-lain-lain">{{ number_format($totalBiayaLain2,0,".",",") }}</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;" colspan="5">Grand Total</td>
                                        <td style="text-align: right">
                                            @if (old('supplier_type_id')==10)
                                                @php
                                                    $grandTotalTerbayar = $totTerbayar+$ro_vat_total+
                                                        (float)str_replace(",","",old('admin_bank'))+
                                                        (float)str_replace(",","",old('biaya_kirim'))+
                                                        (float)str_replace(",","",old('biaya_lainnya'))+
                                                        (float)str_replace(",","",old('biaya_asuransi'))-
                                                        (float)str_replace(",","",old('diskon_pembelian'));
                                                @endphp
                                            @else
                                                @php
                                                    $grandTotalTerbayar = $totTerbayar+($ro_vat==0?0:($totTerbayar*$ro_vat/100))+
                                                        (float)str_replace(",","",old('admin_bank'))+
                                                        (float)str_replace(",","",old('biaya_kirim'))+
                                                        (float)str_replace(",","",old('biaya_lainnya'))+
                                                        (float)str_replace(",","",old('biaya_asuransi'))-
                                                        (float)str_replace(",","",old('diskon_pembelian'));
                                                @endphp
                                            @endif
                                            <input type="hidden" name="grandTotalTerbayar" id="grandTotalTerbayar"
                                                value="@if (old('grandTotalTerbayar')){{ old('grandTotalTerbayar') }}@else{{ $grandTotalTerbayar }}@endif">
                                            <label for="" id="grand-tot-terbayar">{{ number_format($grandTotalTerbayar,0,".",",") }}</label>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="input-group">
                                {{-- <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row" {{ $disabled }}>
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row" {{ $disabled }}> --}}
                                <input type="hidden" name="other_fee_status" id="other_fee_status" value="@if(old('other_fee_status')){{ old('other_fee_status') }}@endif">
                            </div>
                        </div>
                    </div>
                    <div id="other-fee" class="card" style="margin-top: 15px;">
                        @php
                            $readonlyOtherFee = '';
                            $bgColor = '#fff';
                        @endphp
                        @if (old('payment_mode_id')==1)
                            @php
                                $readonlyOtherFee = 'readonly=""';
                                $bgColor = 'gray';
                            @endphp
                        @endif
                        @if (old('payment_mode_id')==2 || old('payment_mode_id')==3)
                            @php
                                $readonlyOtherFee = '';
                                $bgColor = '#fff';
                            @endphp
                        @endif
                        <div class="card-body">
                            <div class="row mb-3" id="admin-bank-row">
                                <label for="admin_bank" class="col-sm-3 col-form-label">Admin Bank ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('admin_bank') is-invalid @enderror"
                                        id="admin_bank" name="admin_bank" placeholder="0" {{ $readonlyOtherFee }} 
                                        value="@if(old('admin_bank')){{ old('admin_bank') }}@else{{ 0 }}@endif" style="background-color: {{ $bgColor }};">
                                    @error('admin_bank')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="admin_bank" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_asuransi" class="col-sm-3 col-form-label">Biaya Asuransi ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('biaya_asuransi') is-invalid @enderror"
                                        id="biaya_asuransi" name="biaya_asuransi" placeholder="0" value="@if(old('biaya_asuransi')){{ old('biaya_asuransi') }}@else{{ 0 }}@endif">
                                    @error('biaya_asuransi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="biaya_asuransi" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Biaya Kirim ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('biaya_kirim') is-invalid @enderror"
                                        id="biaya_kirim" name="biaya_kirim" placeholder="0" value="@if(old('biaya_kirim')){{ old('biaya_kirim') }}@else{{ 0 }}@endif">
                                    @error('biaya_kirim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3" id="biaya-lainnya-row">
                                <label for="biaya_lainnya" class="col-sm-3 col-form-label">Biaya Lainnya ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('biaya_lainnya') is-invalid @enderror"
                                        id="biaya_lainnya" name="biaya_lainnya" placeholder="0" value="@if(old('biaya_lainnya')){{ old('biaya_lainnya') }}@else{{ 0 }}@endif">
                                    @error('biaya_lainnya')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Diskon Pembelian ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('diskon_pembelian') is-invalid @enderror"
                                        id="diskon_pembelian" name="diskon_pembelian" placeholder="0" value="@if(old('diskon_pembelian')){{ old('diskon_pembelian') }}@else{{ 0 }}@endif">
                                    @error('diskon_pembelian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Credit</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    <input type="hidden" name="ro_vat" id="ro_vat" value="@if(old("ro_vat")){{ old("ro_vat") }}@endif">
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    <input type="button" id="save-plan" class="btn btn-primary px-5" value="Save Plan">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@php
    $invoiceNo = '';
@endphp
@foreach($receiptOrders as $p)
    @php
        $invoiceNo .= '<option value="'.$p->id.'">'.$p->invoice_no .'</option>';
    @endphp
@endforeach

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function formatPartPrice(elm){
        let priceList = $(elm).val().replaceAll(',','');
        if(priceList===''){priceList = 0;$(elm).val(0);}
        if(isNaN(priceList)){priceList = 0;$(elm).val(0);}
        priceList = parseFloat(priceList).numberFormat(0,'.',',');
        $(elm).val(priceList);

        sumTerbayar();
    }

    function sumTerbayar(){
        let totRows = parseInt($("#totalRow").val());
        let totNum = 0;
        let totVatNum = 0;
        for(let iRow=0;iRow<totRows;iRow++){
            if (typeof $('#total_inv_'+iRow).val() !== "undefined"){
                let total_inv_per_ro = $('#total_inv_'+iRow).val().replaceAll(',','');
                totNum += parseFloat(total_inv_per_ro);

                if ($("#supplier_type_id").val()==10){
                    // impor
                    totVatNum += ((parseFloat(total_inv_per_ro)/parseFloat($("#total_before_vat_ro_"+iRow).val()))*$("#total_vat_ro_"+iRow).val());
                }else{
                    // domestik
                }
            }
        }
        $('#tot-terbayar').text(parseFloat(totNum).numberFormat(0,'.',','));

        let adminBank = ($('#admin_bank').val()?$('#admin_bank').val().replaceAll(',',''):0);
        let biayaKirim = ($('#biaya_kirim').val()?$('#biaya_kirim').val().replaceAll(',',''):0);
        let biayaLainnya = ($('#biaya_lainnya').val()?$('#biaya_lainnya').val().replaceAll(',',''):0);
        let biayaAsuransi = ($('#biaya_asuransi').val()?$('#biaya_asuransi').val().replaceAll(',',''):0);
        let diskonPembelian = ($('#diskon_pembelian').val()?$('#diskon_pembelian').val().replaceAll(',',''):0);
        $('#biaya-lain-lain').text((parseFloat(adminBank)+
            parseFloat(biayaKirim)+
            parseFloat(biayaLainnya)+
            parseFloat(biayaAsuransi)-
            parseFloat(diskonPembelian)).numberFormat(0,'.',','));

        let vat = $('#ro_vat').val();
        let vat_num = 0;
        let grandTotal = 0;
        if ($('#payment_type_id option:selected').val()=='P'){
            // vat = {{ $qVat->numeric_val }};
            // vat_num = (totNum*vat)/100;
            if ($("#supplier_type_id").val()==10){
                // impor
                vat_num = totVatNum;
            }else{
                // domestik
                vat_num = $('#ro_vat').val()==0?0:(totNum*vat)/100;
            }
            
            $('#vat-terbayar').text(parseFloat(vat_num).numberFormat(0,'.',','));
            grandTotal = parseFloat(totNum+vat_num)+
                parseFloat(adminBank)+
                parseFloat(biayaKirim)+
                parseFloat(biayaLainnya)+
                parseFloat(biayaAsuransi)-
                parseFloat(diskonPembelian);
            $('#grand-tot-terbayar').text(grandTotal.numberFormat(0,'.',','));
        }else{
            grandTotal = parseFloat(totNum)+
                parseFloat(adminBank)+
                parseFloat(biayaKirim)+
                parseFloat(biayaLainnya)+
                parseFloat(biayaAsuransi)-
                parseFloat(diskonPembelian);
            $('#grand-tot-terbayar').text(grandTotal.numberFormat(0,'.',','));
        }
        $('#grandTotalTerbayar').val(grandTotal);
    }

    function dispTotPrice(roid, idx){
        $("#ro_and_retur_"+idx).html("");
        $("#retur_val_"+idx).html("");
        $("#retur_val_o_"+idx).val("");

        var fd = new FormData();
        fd.append('roid', roid);
        $.ajax({
            url: "{{ url('/disp_receiptorder_totalprice_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].receipt_orders;
                let totRo = o.length;
                if (totRo > 0) {
                    let exchange_rate = 1;
                    let exch_rate_for_vat = 1;
                    // let exchange_rate = (o[0].exchange_rate>0 && o[0].exchange_rate!=null)?o[0].exchange_rate:1;
                    // let exch_rate_for_vat = (o[0].exc_rate_for_vat>0 && o[0].exc_rate_for_vat!=null)?o[0].exc_rate_for_vat:1;

                    $("#supplier_type_id").val(o[0].supplier_type_id);
                    $("#total_vat_ro_"+idx).val(o[0].total_vat);
                    $("#total_before_vat_ro_"+idx).val(o[0].total_price_before_vat);
                    $("#total_inv_lbl_"+idx).text(parseFloat(o[0].total_price*exchange_rate).numberFormat(0,'.',','));    // total sebelum dikurangi retur
                    $("#ro_and_retur_"+idx).text(o[0].receipt_no);
                    $("#ro_date_"+idx).text(o[0].receipt_date_01);
                    $("#receipt_id_"+idx).val(o[0].id);
                    $("#total_inv_before_retur_"+idx).val(o[0].total_price*exchange_rate);        // total sebelum dikurangi retur

                    let p = res[0].purchase_returs;
                    let retur_no = '';
                    let retur_val = '';
                    let retur_val_num = 0;
                    let total_inv = o[0].total_price*exchange_rate;
                    if (p.length>0){
                        for (let val=0;val<p.length;val++){
                            retur_no += '<br/>'+p[val].purchase_retur_no;       // no purchase retur
                            retur_val += '('+parseFloat(p[val].total_before_vat*exchange_rate).numberFormat(0,'.',',')+')<br/>';       // value tiap no purchase retur
                            retur_val_num += parseFloat(p[val].total_before_vat*exchange_rate);    // total value dari purchase retur
                            total_inv = parseFloat(total_inv) - parseFloat(p[val].total_before_vat*exchange_rate);     // total yg harus dibayar setelah dikurangi retur jika ada
                        }
                        $("#ro_and_retur_"+idx).html($("#ro_and_retur_"+idx).text()+retur_no);
                        $("#retur_val_"+idx).html(retur_val);   // nilai setiap retur setelah vat dg RO sama
                        $("#retur_val_o_"+idx).val(retur_val_num);   // nilai total retur setelah vat dg RO sama
                    }

                    // nilai final dari total yg harus dibayar setelah dikurangi retur jika ada
                    $("#total_inv_"+idx).val(parseFloat(total_inv).numberFormat(0,'.',','));
                    $("#total_inv_o_"+idx).val(total_inv);

                    sumTerbayar();
                }else{
                    $("#total_inv_"+idx).val(0);
                    $("#total_inv_o_"+idx).val(0);
                    $("#ro_and_retur_"+idx).text('');
                    $("#retur_val_"+idx).text('');
                    $("#retur_val_o_"+idx).val(0);
                }
            },
        });
    }

    function genInvoiceBySupplier(idx){
        $("#invoice_no_"+idx).empty();
        $("#invoice_no_"+idx).append(`<option value="#">Choose...</option>`);

        var fd = new FormData();
        fd.append('supplier_id', $('#supplier_id option:selected').val());
        $.ajax({
            url: "{{ url('/disp_invoice_no_by_supplier') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].receipt_orders;
                let totRo = o.length;
                if (totRo > 0) {
                    for (let i = 0; i < totRo; i++) {
                        optionText = o[i].invoice_no;
                        optionValue = o[i].id;
                        $("#ref_id").append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
                    }
                }
            },
        });
    }

    function addRO(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;"><label for="" class="col-form-label">'+rowNo+'.</label></th>'+
            '<td>'+
            '<select onchange="dispTotPrice(this.value, '+totalRow+');" class="form-select single-select" id="invoice_no_'+totalRow+'" name="invoice_no_'+totalRow+'">'+
            '<option value="#">Choose...</option>'+
            '</select>'+
            '</td>'+
            '<td><label id="ro_date_'+totalRow+'" class="col-form-label"></label></td>'+
            '<td>'+
            '<input type="hidden" id="receipt_id_'+totalRow+'" name="receipt_id_'+totalRow+'" value="" />'+
            '<label id="ro_and_retur_'+totalRow+'" class="col-form-label"></label>'+
            '</td>'+
            '<td><textarea class="form-control" name="desc_'+totalRow+'" id="desc_'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>'+
            '<td style="text-align:right;">'+
            '<input type="hidden" id="total_before_vat_ro_'+totalRow+'" name="total_before_vat_ro_'+totalRow+'" value="" />'+
            '<input type="hidden" id="total_vat_ro_'+totalRow+'" name="total_vat_ro_'+totalRow+'" value="" />'+
            '<input type="hidden" id="total_inv_before_retur_'+totalRow+'" name="total_inv_before_retur_'+totalRow+'" value="" />'+
            '<input type="hidden" id="total_inv_o_'+totalRow+'" name="total_inv_o_'+totalRow+'" value="" />'+
            '<input type="hidden" id="retur_val_o_'+totalRow+'" name="retur_val_o_'+totalRow+'" value="" />'+
            '<label id="total_inv_lbl_'+totalRow+'" class="col-form-label" style="padding-bottom:0;"></label><br/>'+
            '<label id="retur_val_'+totalRow+'" class="col-form-label" style="color: red;padding-top:0;"></label>'+
            '</td>'+
            '<td style="text-align:right;">'+
            '<input onkeyup="formatPartPrice($(this));" type="text" class="form-control" style="text-align: right;" id="total_inv_'+totalRow+'" name="total_inv_'+totalRow+'" '+
            'value="" />'+
            '</td>'+
            '<td style="text-align:right;"><label class="col-form-label">0</label></td>'+
            '<td style="text-align:center;">&nbsp;</td>'+
            // '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'" style="margin-top: 10px;" /></td>'+
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);

        $("#invoice_no_"+totalRow).empty();
        $("#invoice_no_"+totalRow).append(`<option value="#">Choose...</option>`);
        var fd = new FormData();
        fd.append('supplier_id', $("#supplier_id").val());
        fd.append('payment_type_id', $("#payment_type_id").val());
        fd.append('journal_type_id', $("#journal_type_id").val());
        if ($("#tagihan_supplier_id").val()==='#'){
            fd.append('is_ts', 'N');
            fd.append('tagihan_supplier_id', '#');
        }else{
            fd.append('is_ts', 'Y');
            fd.append('tagihan_supplier_id', $("#tagihan_supplier_id").val());
        }
        $.ajax({
            url: "{{ url('/disp_inv_per_supplier_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].receipt_orders;
                let totRo = o.length;
                if (totRo > 0) {
                    for (let i = 0; i < totRo; i++) {
                        optionText = o[i].invoice_no;
                        optionValue = o[i].id;
                        $("#invoice_no_"+totalRow).append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
                        $("#ro_vat").val(o[i].vat_val);
                    }
                }
            },
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width')?$(this).data('width') : $(this).hasClass(
                'w-100')?'100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    }

    $(document).ready(function() {
        $("#supplier_id").change(function() {
            $("#payment_type_id").val('').change();
            $("#new-row").empty();
            $("#totalRow").val(0);

            $("#tagihan_supplier_id").empty();
            $("#tagihan_supplier_id").append(`<option value="#">Choose...</option>`);

            $('#tot-terbayar').text(0);
            $('#vat-terbayar').text(0);
            $('#grand-tot-terbayar').text(0);
            $('#grandTotalTerbayar').val(0);

            var fd = new FormData();
            fd.append('id', $('#supplier_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_supplier_bank_info_by_id') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].banks;
                    let totRo = o.length;
                    if (totRo > 0) {
                        let txt = '';
                        for (let i = 0; i < totRo; i++) {
                            txt += '- '+o[i].bank_name+', no rek '+o[i].account_no+', an. '+o[i].account_name+'||';
                        }
                        $("#supplier-bank-info-detail").html(txt.replace("||", "<br/>"));
                    }
                },
            });
        });

        $("#payment_type_id").change(function() {
            $("#payment_mode_id").val('#').change();
            $("#new-row").empty();
            $("#totalRow").val(0);

            $("#tagihan_supplier_id").empty();
            $("#tagihan_supplier_id").append(`<option value="#">Choose...</option>`);

            $('#tot-terbayar').text(0);
            $('#vat-terbayar').text(0);
            $('#grand-tot-terbayar').text(0);
            $('#grandTotalTerbayar').val(0);

            let payment_type_id = $("#payment_type_id option:selected").val();
            $("#journal_type_id option[value='"+payment_type_id+"']").prop("selected", true).change();
        });

        $("#tagihan_supplier_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);
            $("#coa_id").val('').change();

            var fd = new FormData();
            fd.append('tagihan_supplier_id', $('#tagihan_supplier_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_tagihan_supplier_dtl') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].tagihan_supplier_details;
                    let totRo = o.length;
                    if (totRo > 0) {
                        for (let i = 0; i < totRo; i++) {
                            addRO();
                            $("#coa_id").val(o[i].bank_id).change();
                        }
                    }
                },
            });
        });

        $("#coa_id").change(function() {
            if ($("#tagihan_supplier_id").val()!=='#' && $("#coa_id").val()!=='' && !isNaN($("#coa_id").val())){                
                var fd = new FormData();
                fd.append('tagihan_supplier_id', $('#tagihan_supplier_id option:selected').val());
                $.ajax({
                    url: "{{ url('/disp_tagihan_supplier_dtl') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].tagihan_supplier_details;
                        let totRo = o.length;
                        if (totRo > 0) {
                            let totalRowTmp = $("#totalRow").val();
                            let j = 0;
                            for (let i = 0; i < totalRowTmp; i++) {
                                if ($("#invoice_no_"+i).length){
                                    // $("#invoice_no_"+i).val(o[j].receipt_order_id).change();
                                    $("#invoice_no_"+i).val(o[j].receipt_order_id).trigger('change');
                                    
                                    // var optionValues = $("#invoice_no_"+i).find("option").map(function() {
                                    //     return $(this).val();
                                    // }).get();
                                    // for (let iOpt = 0; iOpt < optionValues.length; iOpt++) {
                                    //     if (Number(optionValues[iOpt]) !== Number(o[j].receipt_order_id)){
                                    //         $('#invoice_no_'+i+' option[value="'+optionValues[iOpt]+'"]').attr('disabled', 'disabled'); // Adds 'disabled' attribute with the option's index
                                    //     }
                                    // }
                                    j++;

                                    // non aktifkan semua input jika invoice diambil dari tagihan supplier
                                    $('#desc_'+i).prop('readonly', true);
                                    // $('#total_inv_'+i).prop('readonly', true);
                                    $('#rowCheck'+i).prop('disabled', true);
                                }
                            }
                        }
                    },
                });

                // non aktifkan tombol add dan delete row jika invoice diambil dari tagihan supplier
                $('#btn-add-row').prop('disabled', true);
                $('#btn-del-row').prop('disabled', true);
            }else{
                // aktifkan tombol add dan delete row jika invoice diambil dari tagihan supplier
                $('#btn-add-row').prop('disabled', false);
                $('#btn-del-row').prop('disabled', false);

                $("#new-row").empty();
                $("#totalRow").val(0);
            }
        });

        $("#ref_id").change(function() {
            $("#tagihan_supplier_id").empty();
            $("#tagihan_supplier_id").append(`<option value="#">Choose...</option>`);

            if (!isNaN($('#supplier_id option:selected').val()) && (parseInt($("#ref_id").val())===50 || parseInt($("#ref_id").val())===63)) {                
                var fd = new FormData();
                fd.append('supplier_id', $('#supplier_id option:selected').val());
                fd.append('payment_type_id', $('#payment_type_id option:selected').val());
                fd.append('payment_mode_id', $('#payment_mode_id option:selected').val());
                fd.append('pv_id', 0);
                $.ajax({
                    url: "{{ url('/disp_tagihan_supplier') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].tagihan_suppliers;
                        let totRo = o.length;
                        if (totRo > 0) {
                            for (let i = 0; i < totRo; i++) {
                                optionText = o[i].tagihan_supplier_no;
                                optionValue = o[i].id;
                                $("#tagihan_supplier_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            }
        });

        $("#journal_type_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);

            $('#tot-terbayar').text(0);
            $('#vat-terbayar').text(0);
            $('#grand-tot-terbayar').text(0);
            $('#grandTotalTerbayar').val(0);
        });

        $("#payment_mode_id").change(function() {
            $("#ref_id").empty();
            $("#ref_id").append(`<option value="#">Choose...</option>`);
            $("#tagihan_supplier_id").empty();
            $("#tagihan_supplier_id").append(`<option value="#">Choose...</option>`);
            $("#coa_id").empty();
            $("#coa_id").append(`<option value="">Choose...</option>`);

            if ($('#payment_mode_id option:selected').val()==1){
                $('#admin_bank').val(0);
                $('#admin_bank').css('background-color','gray');
                $('input[name="admin_bank"]').attr('readonly', true);
            }
            if ($('#payment_mode_id option:selected').val()==2 ||$('#payment_mode_id option:selected').val()==3){
                $('#admin_bank').css('background-color','white');
                $('#admin_bank').removeAttr('readonly');
            }

            // isi opsi tagihan supplier
            var fd1 = new FormData();
            fd1.append('supplier_id', $('#supplier_id option:selected').val());
            fd1.append('payment_type_id', $('#payment_type_id option:selected').val());
            fd1.append('payment_mode_id', $('#payment_mode_id option:selected').val());
            fd1.append('pv_id', 0);
            $.ajax({
                url: "{{ url('/disp_tagihan_supplier') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd1,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].tagihan_suppliers;
                    let totRo = o.length;
                    if (totRo > 0) {
                        for (let i = 0; i < totRo; i++) {
                            optionText = o[i].tagihan_supplier_no;
                            optionValue = o[i].id;
                            $("#tagihan_supplier_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    }
                },
            });

            // isi opsi no rekening
            var fd2 = new FormData();
            fd2.append('supplier_id', $('#supplier_id option:selected').val());
            fd2.append('payment_group', ($('#payment_type_id option:selected').val()=='P'?8:13));
            fd2.append('payment_mode_id', $('#payment_mode_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_bankaccno') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd2,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].bankaccno;
                    let totRo = o.length;
                    if (totRo > 0) {
                        for (let i = 0; i < totRo; i++) {
                            optionText = o[i].coa_name;
                            optionValue = o[i].id;
                            $("#coa_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    }
                },
            });

            if($('#payment_mode_id option:selected').val()==2){
                var fd1 = new FormData();
                fd1.append('payment_mode_id', $('#payment_mode_id option:selected').val());
                $.ajax({
                    url: "{{ url('/disp_payment_ref') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd1,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].refs;
                        let totRo = o.length;
                        if (totRo > 0) {
                            for (let i = 0; i < totRo; i++) {
                                optionText = o[i].title_ind;
                                optionValue = o[i].id;
                                $("#ref_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            }
        });

        $("#save-as-draft").click(function() {
            if(!confirm("Data will be saved to database with DRAFT status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#save-plan").click(function() {
            if(!confirm("Data will be saved to database with PLAN status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('P');
                $("#submit-form").submit();
            }
        });

        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            $('#reference_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#payment_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            if($("#supplier_id").val()==='#'){
                alert('Please select a valid supplier');
                $("#supplier_id").focus();
                return false;
            }
            if($("#payment_type_id").val()===''){
                alert('Please select NPWP type');
                $("#payment_type_id").focus();
                return false;
            }
            if($("#journal_type_id").val()===''){
                alert('Please select Journal type');
                $("#journal_type_id").focus();
                return false;
            }

            addRO();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width')?$(this).data('width') : $(this).hasClass('w-100')?'100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
