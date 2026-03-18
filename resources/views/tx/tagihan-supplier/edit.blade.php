@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}
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
    .part-id {
        font-size: large !important;
        font-weight: 700;
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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.urlencode($qTS->tagihan_supplier_no)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-xl-7">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">No Tagihan Supplier</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qTS->tagihan_supplier_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                                        id="supplier_id" name="supplier_id" onchange="dispReceiptOrder(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $supplier_id = (old('supplier_id')?old('supplier_id'):$qTS->supplier_id);
                                                        @endphp
                                                        @foreach ($qSuppliers as $sp)
                                                            <option @if ($supplier_id==$sp->id){{ 'selected' }}@endif
                                                                value="{{ $sp->id }}">{{ $sp->supplier_code.' - '.(!is_null($sp->entity_type)?$sp->entity_type->title_ind:'').
                                                                ' '.$sp->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="receipt_order_id" class="col-sm-3 col-form-label">INV No / RO No*</label>
                                                <div class="col-sm-6">
                                                    <select class="form-select single-select @error('receipt_order_no_all') is-invalid @enderror" id="receipt_order_id" name="receipt_order_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $receipt_order_id = (old('receipt_order_id')?old('receipt_order_id'):0);
                                                        @endphp
                                                        @foreach ($qRO as $ro)
                                                            <option value="{{ $ro->id }}">{{ $ro->invoice_no.' / '.$ro->receipt_no.' ('.($ro->journal_type_id!='N'?'VAT':'Non VAT').')' }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('receipt_order_no_all')
                                                        <div class="invalid-feedback col-sm-8">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <input type="button" name="gen_ro_row" id="gen_ro_row" class="btn btn-primary px-5 col-sm-3" value="Generate">                                                                                                
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">INV No / RO No</th>
                                                                        <th scope="col" style="width: 3%;">Delete</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="new-row-ro">
                                                                    @php
                                                                        $vat_val = '';
                                                                        $iRow = 0;
                                                                    @endphp
                                                                    @if(old('receipt_order_no_all'))
                                                                        @php
                                                                            $receipt_order_no_all = explode(',', old('receipt_order_no_all'));
                                                                        @endphp
                                                                        @foreach ($receipt_order_no_all as $sNo)
                                                                            @if ($sNo!='')
                                                                                @php
                                                                                    $sNoTmp = str_replace("(Non VAT)", "", $sNo);
                                                                                    $sNoTmp = str_replace("(VAT)", "", $sNoTmp);
                                                                                    $ro_inv = explode(' / ', $sNoTmp);
                                                                                    $qS = \App\Models\Tx_receipt_order::where('receipt_no', '=', trim($ro_inv[count($ro_inv)-1], " "))
                                                                                    ->first();
                                                                                @endphp
                                                                                @if ($qS)                                                                                
                                                                                    <tr id="rowRO{{ $iRow }}">
                                                                                        <td scope="row" style="text-align:right;">
                                                                                            <label for="" class="col-form-label" id="ro_row_number{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                                                            <input type="hidden" name="receipt_order_idRO{{ $iRow }}" id="receipt_order_idRO{{ $iRow }}" 
                                                                                                value="{{ $qS->id }}">
                                                                                        </td>
                                                                                        <td>
                                                                                            <label for="" name="receipt_order_no_select{{ $iRow }}" id="receipt_order_no_select{{ $iRow }}"
                                                                                                class="col-form-label">{{ $sNoTmp }}</label>
                                                                                        </td>
                                                                                        <td style="text-align:center;">
                                                                                            <input type="checkbox" id="rowCheckRO{{ $iRow }}" value="{{ $iRow }}">
                                                                                        </td>
                                                                                    </tr>
                                                                                    @php
                                                                                        $vat_val = ($qS->vat_val>0?'VAT':'Non VAT');
                                                                                        $iRow+= 1;
                                                                                    @endphp
                                                                                @endif
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        @php
                                                                            $iRow = 0;
                                                                            $receipt_order_no_all = '';
                                                                        @endphp
                                                                        @foreach ($qRO_selected->get() as $qRO_s)
                                                                            <tr id="rowRO{{ $iRow }}">
                                                                                <td scope="row" style="text-align:right;">
                                                                                    <label for="" class="col-form-label" id="ro_row_number{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                                                    <input type="hidden" name="ts_dtl{{ $iRow }}" id="ts_dtl{{ $iRow }}" value="{{ $qRO_s->qTS_dtl_id }}">
                                                                                    <input type="hidden" name="receipt_order_idRO{{ $iRow }}" id="receipt_order_idRO{{ $iRow }}" 
                                                                                        value="{{ $qRO_s->ro_id }}">
                                                                                </td>
                                                                                <td>
                                                                                    @php
                                                                                        $ro_lbl = $qRO_s->invoice_no.' / '.$qRO_s->receipt_no.' ('.($qRO_s->journal_type_id!='N'?'VAT':'Non VAT').')';
                                                                                    @endphp
                                                                                    <label for="" name="receipt_order_no_select{{ $iRow }}" id="receipt_order_no_select{{ $iRow }}"
                                                                                        class="col-form-label">{{ $ro_lbl }}</label>
                                                                                </td>
                                                                                <td style="text-align:center;">
                                                                                    <input type="checkbox" id="rowCheckRO{{ $iRow }}" value="{{ $iRow }}">
                                                                                </td>
                                                                            </tr>
                                                                            @php
                                                                                $receipt_order_no_all .= $ro_lbl.',';
                                                                                $vat_val = ($qRO_s->journal_type_id!='N'?'VAT':'Non VAT');
                                                                                $iRow+= 1;
                                                                            @endphp
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                            <input type="hidden" id="totalRowRO" name="totalRowRO" value="@if(old('totalRowRO')){{ $iRow }}@else{{ $qRO_selected->count() }}@endif">
                                                            <input type="button" id="btn-del-row-ro" class="btn btn-danger px-5 mt-3" value="Remove Row">
                                                            <input type="hidden" name="receipt_order_no_all" id="receipt_order_no_all" 
                                                                value="@if(old('receipt_order_no_all')){{ old('receipt_order_no_all') }}@else{{ $receipt_order_no_all }}@endif">
                                                            <input type="hidden" name="vat_val" id="vat_val" value="@if(old('vat_val')){{ old('vat_val') }}@else{{ '('.$vat_val.')' }}@endif">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="payment_plan_date" class="col-sm-3 col-form-label">Payment Plan Date*</label>
                                                <div class="col-sm-9">
                                                    @php
                                                        $date=date_create($qTS->tagihan_supplier_date);
                                                    @endphp 
                                                    <input readonly type="text" class="form-control @error('payment_plan_date') is-invalid @enderror"
                                                        maxlength="10" id="payment_plan_date" name="payment_plan_date" placeholder="Payment Plan Date"
                                                        value="@if(old('payment_plan_date')){{ old('payment_plan_date') }}@else{{ date_format($date,"d/m/Y") }}@endif">
                                                    @error('payment_plan_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <span class="col-sm-3 col-form-label">Payment Via*</span>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $bank_id = old('bank_id')?old('bank_id'):$qTS->bank_id;
                                                        @endphp
                                                        @foreach ($coas as $coa)
                                                            <option @if ($bank_id==$coa->id) {{ 'selected' }} @endif value="{{ $coa->id }}">
                                                                {{ $coa->coa_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('bank_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Receipt Order & Invoice</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $lastTotalAmount = 0;
                            @endphp
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 24%;">RO No</th>
                                        <th scope="col" style="width: 24%;">INV No</th>
                                        <th scope="col" style="width: 24%;">PR No</th>
                                        <th scope="col" style="width: 25%;">Total Price ({{ $qCurrency->string_val }})</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $iRow = 0;
                                        $lastVatTmp = 0;
                                        $lastVatVal = 0;
                                    @endphp
                                    @if (old('receipt_order_no_all'))
                                        @foreach ($receipt_order_no_all as $sNo)
                                            @if ($sNo!='')
                                                @php
                                                    $sNoTmp = str_replace("(Non VAT)", "", $sNo);
                                                    $sNoTmp = str_replace("(VAT)", "", $sNoTmp);
                                                    $ro_inv = explode(' / ', $sNoTmp);

                                                    $qS = \App\Models\Tx_receipt_order::leftJoin('tx_purchase_returs as tx_pr', 'tx_receipt_orders.id', '=', 'tx_pr.receipt_order_id')
                                                    ->select(
                                                        'tx_receipt_orders.id as ro_id',
                                                        'tx_receipt_orders.receipt_no',
                                                        'tx_receipt_orders.invoice_no',
                                                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                            tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
                                                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                            tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
                                                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                            tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
                                                        'tx_receipt_orders.vat_val',
                                                        'tx_receipt_orders.journal_type_id',
                                                        'tx_receipt_orders.active as ro_active',
                                                    )
                                                    ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                                                    ->where([
                                                        'tx_receipt_orders.receipt_no' => trim($ro_inv[count($ro_inv)-1], " "),
                                                        'tx_receipt_orders.active' => 'Y',
                                                    ])
                                                    ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                                                    ->first();
                                                @endphp
                                                @if ($qS)
                                                    @php
                                                        $pr_total_before_vat = 0;
                                                        $purchase_retur_no = '';
                                                        $pr_total_vat = 0;
                                                        $qPR = \App\Models\Tx_purchase_retur::where('receipt_order_id', $qS->ro_id)
                                                        ->whereRaw('approved_by IS NOT NULL')
                                                        ->where('is_draft', 'N')
                                                        ->where('active', 'Y')
                                                        ->get();
                                                        foreach ($qPR as $pr) {
                                                            $purchase_retur_no .= $pr->purchase_retur_no.'<br/>';
                                                            $pr_total_before_vat += $pr->total_before_vat;
                                                            $pr_total_vat += ($pr->total_after_vat-$pr->total_before_vat);
                                                        }
                                                    @endphp 
                                                    <tr id="rowROdtl{{ $iRow }}">
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" class="col-form-label" id="ro_row_number_dtl{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                            <input type="hidden" name="receipt_order_id_dtl_{{ $iRow }}" id="receipt_order_id_dtl_{{ $iRow }}" value="{{ $qS->ro_id }}">
                                                            <input type="hidden" name="vat_dtl_{{ $iRow }}" id="vat_dtl_{{ $iRow }}" value="{{ $qS->vat_val }}">
                                                            <input type="hidden" name="vat_num_{{ $iRow }}" id="vat_num_{{ $iRow }}" value="{{ $qS->ro_total_vat-$pr_total_vat }}">
                                                        </td>
                                                        <td><label for="" name="ro_no_{{ $iRow }}" id="ro_no_{{ $iRow }}" class="col-form-label">{{ $qS->receipt_no }}</label></td>
                                                        <td><label for="" name="inv_no_{{ $iRow }}" id="inv_no_{{ $iRow }}" class="col-form-label">{{ $qS->invoice_no }}</label></td>
                                                        <td>
                                                            <label for="" name="prc_retur_no_{{ $iRow }}" id="prc_retur_no_{{ $iRow }}" class="col-form-label">
                                                                {!! $purchase_retur_no !!}
                                                            </label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" name="total_price_{{ $iRow }}" id="total_price_{{ $iRow }}" 
                                                                class="col-form-label">{{ number_format($qS->ro_total_before_vat-$pr_total_before_vat,0,'.',',') }}
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $lastVatTmp = $qS->vat_val;
                                                        $lastVatVal += ($qS->ro_total_vat-$pr_total_vat);
                                                        $lastTotalAmount += ($qS->ro_total_before_vat-$pr_total_before_vat);
                                                    @endphp
                                                @endif
                                                @php
                                                    $iRow++;
                                                @endphp
                                            @endif
                                        @endforeach
                                        <tr id="rowTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalAmount" id="lblTotalAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                            </td>
                                        </tr>
                                        <tr id="rowVAT">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblVATAmount" id="lblVATAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastVatVal,0,'.',',') }}
                                                </label>
                                            </td>
                                        </tr>
                                        <tr id="rowGrandTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastVatVal),0,'.',',') }}
                                                </label>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($qRO_selected->get() as $qRO_s)
                                            @php
                                                $qS = \App\Models\Tx_receipt_order::select(
                                                    'tx_receipt_orders.id as ro_id',
                                                    'tx_receipt_orders.receipt_no',
                                                    'tx_receipt_orders.invoice_no',
                                                    DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                        tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
                                                    DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                        tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
                                                    DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                        tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
                                                    'tx_receipt_orders.vat_val',
                                                    'tx_receipt_orders.active as ro_active',
                                                )
                                                ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                                                ->where([
                                                    'tx_receipt_orders.id' => $qRO_s->ro_id,
                                                    'tx_receipt_orders.active' => 'Y',
                                                ])
                                                ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                                                ->first();
                                            @endphp
                                            @if ($qS)
                                                @php
                                                    $pr_total_before_vat = 0;
                                                    $purchase_retur_no = '';
                                                    $pr_total_vat = 0;
                                                    $qPR = \App\Models\Tx_purchase_retur::where('receipt_order_id', $qRO_s->ro_id)
                                                    ->whereRaw('approved_by IS NOT NULL')
                                                    ->where('is_draft', 'N')
                                                    ->where('active', 'Y')
                                                    ->get();
                                                    foreach ($qPR as $pr) {
                                                        $purchase_retur_no .= $pr->purchase_retur_no.'<br/>';
                                                        $pr_total_before_vat += $pr->total_before_vat;
                                                        $pr_total_vat += ($pr->total_after_vat-$pr->total_before_vat);
                                                    }
                                                @endphp 
                                                <tr id="rowROdtl{{ $iRow }}">
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" class="col-form-label" id="ro_row_number_dtl{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                        <input type="hidden" name="receipt_order_id_dtl_{{ $iRow }}" id="receipt_order_id_dtl_{{ $iRow }}" value="{{ $qS->ro_id }}">
                                                        <input type="hidden" name="vat_dtl_{{ $iRow }}" id="vat_dtl_{{ $iRow }}" value="{{ $qS->vat_val }}">
                                                        <input type="hidden" name="vat_num_{{ $iRow }}" id="vat_num_{{ $iRow }}" value="{{ $qS->ro_total_vat-$pr_total_vat }}">
                                                    </td>
                                                    <td><label for="" name="ro_no_{{ $iRow }}" id="ro_no_{{ $iRow }}" class="col-form-label">{{ $qS->receipt_no }}</label></td>
                                                    <td><label for="" name="inv_no_{{ $iRow }}" id="inv_no_{{ $iRow }}" class="col-form-label">{{ $qS->invoice_no }}</label></td>
                                                    <td>
                                                        <label for="" name="prc_retur_no_{{ $iRow }}" id="prc_retur_no_{{ $iRow }}" class="col-form-label">
                                                            {!! $purchase_retur_no !!}
                                                        </label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" name="total_price_{{ $iRow }}" id="total_price_{{ $iRow }}" 
                                                            class="col-form-label">{{ number_format($qS->ro_total_before_vat-$pr_total_before_vat,0,'.',',') }}
                                                        </label>
                                                    </td>
                                                </tr>
                                                @php
                                                    $lastVatTmp = $qS->vat_val;
                                                    $lastVatVal += ($qS->ro_total_vat-$pr_total_vat);
                                                    $lastTotalAmount += ($qS->ro_total_before_vat-$pr_total_before_vat);
                                                @endphp
                                            @endif
                                            @php
                                                $iRow++;
                                            @endphp
                                        @endforeach
                                        <tr id="rowTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalAmount" id="lblTotalAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                            </td>
                                        </tr>
                                        <tr id="rowVAT">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblVATAmount" id="lblVATAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastVatVal,0,'.',',') }}
                                                </label>
                                            </td>
                                        </tr>
                                        <tr id="rowGrandTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" 
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastVatVal),0,'.',',') }}
                                                </label>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                        @php
                                            $qPv = \App\Models\Tx_payment_voucher::where('tagihan_supplier_id', '=', $qTS->id)
                                            ->where('active', '=', 'Y')
                                            ->first();
                                        @endphp
                                        @if (!$qPv)                        
                                            <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                            <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                            <input type="button" id="remv-btn" class="btn btn-danger px-5" value="Delete">
                                        @endif
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
    let lastTotalAmountTmp = 0;
    let lastVatTmp = {{ $lastVatTmp }};
    let lastVatVal = {{ $lastVatVal }};

    function dispReceiptOrder(supplier_id){
        $("#vat_val").val('');
        $("#receipt_order_id").empty();
        $("#receipt_order_id").append(`<option value="#">Choose...</option>`);
        $("#totalRowRO").val(0);
        $("#receipt_order_no_all").val('');
        $("#bank_id").empty();
        $("#bank_id").append(`<option value="#">Choose...</option>`);
        $("#new-row-ro").empty();
        $("#new-row").empty();

        var fd = new FormData();
        fd.append('supplier_id', supplier_id);
        fd.append('tx_ts_ignore_id', {{ $qTS->id }});
        $.ajax({
            url: "{{ url('/disp_ro_tagihan_supplier') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].receipt_orders;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].invoice_no+' / '+o[i].receipt_no+' ('+(o[i].vat_val>0?'VAT':'Non VAT')+')';
                        optionValue = o[i].id;
                        $("#receipt_order_id").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function dispNoRek(local_init){
        $("#bank_id").empty();
        $("#bank_id").append(`<option value="#">Choose...</option>`);

        var fd = new FormData();
        fd.append('lc', local_init);
        $.ajax({
            url: "{{ url('/disp_ro_tagihan_supplier_norek') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].acc_nos;
                let totLocal = o.length;
                if (totLocal > 0) {
                    for (let i = 0; i < totLocal; i++) {
                        optionText = o[i].coa_name;
                        optionValue = o[i].id;
                        $("#bank_id").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function addRO(){
        let totalRowRO = $("#totalRowRO").val();
        let rowNoRO = (parseInt(totalRowRO)+1);
        let vHtml_01 =
            '<tr id="rowRO'+totalRowRO+'">'+
            '<td scope="row" style="text-align:right;">'+
            '<label for="" class="col-form-label" id="ro_row_number'+totalRowRO+'">'+rowNoRO+'.&nbsp;</label>'+
            '<input type="hidden" name="receipt_order_idRO'+totalRowRO+'" id="receipt_order_idRO'+totalRowRO+'">'+
            '</td>'+
            '<td><label for="" name="receipt_order_no_select'+totalRowRO+'" id="receipt_order_no_select'+totalRowRO+'" class="col-form-label"></label></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheckRO'+totalRowRO+'" value="'+totalRowRO+'"></td>'+
            '</tr>';
        $("#new-row-ro").append(vHtml_01);
        
        let vHtml_02 = 
            '<tr id="rowROdtl'+totalRowRO+'">'+
                '<td scope="row" style="text-align:right;">'+
                '<label for="" class="col-form-label" id="ro_row_number_dtl'+totalRowRO+'">'+rowNoRO+'.&nbsp;</label>'+
                '<input type="hidden" name="ts_dtl'+totalRowRO+'" id="ts_dtl'+totalRowRO+'" value="0">'+
                '<input type="hidden" name="receipt_order_id_dtl_'+totalRowRO+'" id="receipt_order_id_dtl_'+totalRowRO+'">'+
                '<input type="hidden" name="vat_dtl_'+totalRowRO+'" id="vat_dtl_'+totalRowRO+'">'+
                '<input type="hidden" name="vat_num_'+totalRowRO+'" id="vat_num_'+totalRowRO+'">'+
                '</td>'+
                '<td><label for="" name="ro_no_'+totalRowRO+'" id="ro_no_'+totalRowRO+'" class="col-form-label"></label></td>'+
                '<td><label for="" name="inv_no_'+totalRowRO+'" id="inv_no_'+totalRowRO+'" class="col-form-label"></label></td>'+
                '<td><label for="" name="prc_retur_no_'+totalRowRO+'" id="prc_retur_no_'+totalRowRO+'" class="col-form-label"></label></td>'+
                '<td style="text-align: right;"><label for="" name="total_price_'+totalRowRO+'" id="total_price_'+totalRowRO+'" class="col-form-label"></label></td>'+
            '</tr>';
        $("#new-row").append(vHtml_02);
        
        $('#receipt_order_idRO'+(totalRowRO)).val($('#receipt_order_id option:selected').val());
        $('#receipt_order_no_select'+(totalRowRO)).text($('#receipt_order_id option:selected').text());        
        var fd = new FormData();
        fd.append('r', $('#receipt_order_id option:selected').val());
        $.ajax({
            url: "{{ url('/disp_ro_tagihan_supplier_dtl') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].receipt_orders;
                let totOrder = o.length;
                if (totOrder > 0) {
                    $("#rowTotal").remove();
                    $("#rowVAT").remove();
                    $("#rowGrandTotal").remove();
                    
                    let vHtmlTotal =
                        '<tr id="rowTotal">' +
                        '<td colspan="4" style="text-align: right;"><label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label></td>' +
                        '<td style="text-align: right;"><label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}</label></td>' +
                        '</tr>';
                    $("#new-row").append(vHtmlTotal);
                    let vHtmlVAT =
                        '<tr id="rowVAT">' +
                        '<td colspan="4" style="text-align: right;"><label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label></td>' +
                        '<td style="text-align: right;"><label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ $qCurrency->string_val }}</label></td>' +
                        '</tr>';
                    $("#new-row").append(vHtmlVAT);
                    let vHtmlGrandTotal =
                        '<tr id="rowGrandTotal">' +
                        '<td colspan="4" style="text-align: right;"><label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label></td>' +
                        '<td style="text-align: right;"><label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}</label></td>' +
                        '</tr>';
                    $("#new-row").append(vHtmlGrandTotal);

                    let p = res[0].purchase_returs;
                    let totPR = p.length;
                    let purchase_retur_no = '';
                    let pr_total_before_vat = 0;
                    let pr_total_vat = 0;
                    for (let j = 0; j<totPR; j++) {
                        purchase_retur_no += p[j].purchase_retur_no+'<br/>';
                        pr_total_before_vat += p[j].total_before_vat;
                        pr_total_vat += (parseFloat(p[j].total_after_vat)-parseFloat(p[j].total_before_vat));
                    }

                    for (let i = 0; i < totOrder; i++) {
                        $('#receipt_order_id_dtl_'+(totalRowRO)).val(o[i].ro_id);
                        $('#vat_dtl_'+(totalRowRO)).val(o[i].vat_val);
                        $('#vat_num_'+(totalRowRO)).val(parseFloat(o[i].ro_total_vat)-parseFloat(pr_total_vat));
                        $('#ro_no_'+(totalRowRO)).text(o[i].receipt_no);
                        $('#inv_no_'+(totalRowRO)).text(o[i].invoice_no);
                        $('#prc_retur_no_'+(totalRowRO)).html(purchase_retur_no);
                        $('#total_price_'+(totalRowRO)).text(parseFloat(o[i].ro_total_before_vat-pr_total_before_vat).numberFormat(0,'.',','));
                        lastTotalAmountTmp += parseFloat(o[i].ro_total_before_vat-pr_total_before_vat);
                        lastVatTmp = o[i].vat_val;
                        lastVatVal = (parseFloat(o[i].ro_total_vat)-parseFloat(pr_total_vat));

                        if (o[i].journal_type_id!='N'){
                            $("#vat_val").val('(VAT)');
                        }else{
                            $("#vat_val").val('(Non VAT)')
                        }
                    }

                    recalcTotal(0);

                    // reset penomoran
                    let j = 1;
                    for (i = 0; i < $("#totalRowRO").val(); i++) {
                        if($("#ro_row_number"+i).text()){
                            $("#ro_row_number"+i).html(j+'.&nbsp;');
                            $("#ro_row_number_dtl"+i).html(j+'.&nbsp;');
                            j++;
                        }
                    }
                    // reset penomoran - end                    
                }
            }
        });

        $("#totalRowRO").val(rowNoRO);
    }

    function recalcTotal(vat_num){
        let totalRow = $("#totalRowRO").val();
        let totalAmount = 0;
        let VatValNum = 0;
        for(iRow=0;iRow<totalRow;iRow++){
            if ($('#total_price_'+iRow).length){
                let totalAmountPerRow = $('#total_price_'+iRow).text().replaceAll(',','');
                if (!isNaN(totalAmountPerRow)){
                    totalAmount += parseFloat(totalAmountPerRow);
                    VatValNum += parseFloat($('#vat_num_'+iRow).val());
                    lastTotalAmountTmp = totalAmount;
                }
            }
        }
        // console.log(totalAmount);

        // VatValNum = lastVatVal;
        $('#lblTotalAmount').text('{{ $qCurrency->string_val }}'+parseFloat(totalAmount).numberFormat(0,'.',','));
        $('#lblVATAmount').text('{{ $qCurrency->string_val }}'+parseFloat(VatValNum).numberFormat(0,'.',','));
        $('#lblGrandTotalAmount').text('{{ $qCurrency->string_val }}'+((parseFloat(totalAmount)+parseFloat(VatValNum)).numberFormat(0,'.',',')));
    }

    $(document).ready(function() {
        $(function() {
            $('#payment_plan_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD/MM/YYYY',
                time: false
            });
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

        $("#remv-btn").click(function() {
            if(!confirm("Data will be deleted. Once deleted data cannot be restored.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                location.href = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/rm?no='.urlencode($qTS->tagihan_supplier_no)) }}';
            }
        });

        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri) }}";            
        });
        
        $("#btn-del-row-ro").click(function() {
            let all_deleted = false;
            for (i = 0; i < $("#totalRowRO").val(); i++) {
                let RO_No = $("#receipt_order_no_select"+i).text();
                if ($("#rowCheckRO"+i).is(':checked')) {

                    $("#rowRO"+i).remove();
                    $("#rowROdtl"+i).remove();                    

                    $("#receipt_order_no_all").val($("#receipt_order_no_all").val().replace(RO_No,''));
                    $("#receipt_order_no_all").val($("#receipt_order_no_all").val().replace(',,',','));
                    if($("#receipt_order_no_all").val()===','){
                        $("#receipt_order_no_all").val('');
                    }
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRowRO").val(); i++) {
                if($("#ro_row_number"+i).text()){
                    $("#ro_row_number"+i).html(j+'.&nbsp;');
                    $("#ro_row_number_dtl"+i).html(j+'.&nbsp;');
                    j++;
                }
            }
            // reset penomoran - end

            for (i = 0; i < $("#totalRowRO").val(); i++) {
                // cek apakah masih ada no RO yang tersisa
                if($("#ro_row_number"+i).text()){
                    all_deleted = true;
                }
            }
            if (!all_deleted){
                $("#vat_val").val('');
                lastTotalAmountTmp = 0;
            }

            recalcTotal(0);
        });

        $("#gen_ro_row").click(function() {
            if($('#receipt_order_id option:selected').val()=='#'){
                alert('Please select Receipt Order No!');
                $('#receipt_order_no').focus();
                return false;
            }
            if($("#receipt_order_no_all").val().indexOf($('#receipt_order_id option:selected').text())===-1){
                // deteksi VAT atau Non VAT
                let vat_val = '';
                if ($('#receipt_order_id option:selected').text().indexOf('(VAT)')>-1){vat_val = '(VAT)';}
                if ($('#receipt_order_id option:selected').text().indexOf('(Non VAT)')>-1){vat_val = '(Non VAT)';}
                
                if ($("#vat_val").val()==''){
                    if ($('#receipt_order_id option:selected').text().indexOf('(VAT)')>-1){$("#vat_val").val('(VAT)');}
                    if ($('#receipt_order_id option:selected').text().indexOf('(Non VAT)')>-1){$("#vat_val").val('(Non VAT)');}

                    dispNoRek(($("#vat_val").val()=='(VAT)'?'A':'N'));

                }else{
                    if ($("#vat_val").val()!=vat_val){
                        alert('Group RO Not Similar. Choose VAT or Non VAT.');
                        return false;
                    }
                }
                // deteksi VAT atau Non VAT - End

                // agar no order tidak duplikat
                let orderNo = $("#receipt_order_no_all").val()+','+$('#receipt_order_id option:selected').text();
                $("#receipt_order_no_all").val(orderNo);
                
                addRO();
                
            }else{
                alert('The Receipt Order number already exists!');
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
