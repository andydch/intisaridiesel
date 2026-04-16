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
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            @if($userLogin->is_director=='Y')
                                                <div class="row mb-3">
                                                    <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" onchange="dispPoPm('');">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $p_Id = (old('branch_id')?old('branch_id'):0);
                                                            @endphp
                                                            @foreach ($branches as $branch)
                                                                <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('branch_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" name="branch_id" id="branch_id" value="@if(old('branch_id')){{ old('branch_id') }}@else{{ $userLogin->branch_id }}@endif">
                                            @endif
                                            <div class="row mb-3">
                                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                                        id="supplier_id" name="supplier_id" onchange="dispPoPm(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('supplier_id')?old('supplier_id'):0);
                                                        @endphp
                                                        @foreach ($querySupplier as $qS)
                                                            <option @if ($p_Id==$qS->id) {{ 'selected' }} @endif 
                                                                value="{{ $qS->id }}">{{ (!is_null($qS->entity_type)?$qS->entity_type->title_ind:'').' '.$qS->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <input type="hidden" name="supplier_type_id" id="supplier_type_id" value="@if(old('supplier_type_id')){{ old('supplier_type_id') }}@endif">
                                            <div class="row mb-3">
                                                <label for="invoice_no" class="col-sm-3 col-form-label">Invoice No*</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('invoice_no') is-invalid @enderror"
                                                        maxlength="255" id="invoice_no" name="invoice_no" placeholder="Enter Invoice No"
                                                        value="@if (old('invoice_no')){{ old('invoice_no') }}@endif">
                                                    @error('invoice_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label id="invoice_amount_lbl" for="invoice_amount" class="col-sm-3 col-form-label">
                                                    Invoice Amount{{ $currency_code!=""? " (".$currency_code.")": "" }}*
                                                </label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('invoice_amount') is-invalid @enderror"
                                                        maxlength="255" id="invoice_amount" name="invoice_amount" style="text-align: right;"
                                                        placeholder="Enter Invoice Amount" onchange="formatAmount($(this));"
                                                        value="@if (old('invoice_amount')){{ old('invoice_amount') }}@endif">
                                                    @error('invoice_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @php
                                                $readonly = '';
                                            @endphp
                                            @if (old('supplier_id'))
                                                @php
                                                    $qSupplier = \App\Models\Mst_supplier::where('id','=',old('supplier_id'))
                                                    ->first();
                                                @endphp
                                                @if ($qSupplier)
                                                    @if($qSupplier->supplier_type_id==10)
                                                        @php
                                                            $readonly = '';
                                                        @endphp
                                                    @else
                                                        @php
                                                            $readonly = 'readonly';
                                                        @endphp
                                                    @endif
                                                @endif
                                            @endif
                                            <div class="row mb-3">
                                                <label for="exc_rate" class="col-sm-3 col-form-label">Exchange Rate</label>
                                                <div class="col-sm-9">
                                                    <input {{ $readonly }} type="text" class="form-control @error('exc_rate') is-invalid @enderror"
                                                        maxlength="255" id="exc_rate" name="exc_rate" style="text-align: right;"
                                                        placeholder="Enter Exchange Rate" onchange="formatAmount($(this));"
                                                        value="@if(old('exc_rate')){{ old('exc_rate') }}@endif">
                                                    @error('exc_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="vat_import" class="col-sm-3 col-form-label">VAT Import</label>
                                                <div class="col-sm-9">
                                                    <input {{ $readonly }} type="text" class="form-control @error('vat_import') is-invalid @enderror"
                                                        maxlength="255" id="vat_import" name="vat_import" style="text-align: right;"
                                                        placeholder="Enter VAT Import" onchange="formatAmount($(this));"
                                                        value="@if(old('vat_import')){{ old('vat_import') }}@endif">
                                                    @error('vat_import')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            {{-- <div class="row mb-3">
                                                <label for="exch_rate_for_vat" class="col-sm-3 col-form-label">Exchange Rate VAT</label>
                                                <div class="col-sm-9">
                                                    <input {{ $readonly }} type="text" class="form-control @error('exch_rate_for_vat') is-invalid @enderror"
                                                        maxlength="255" id="exch_rate_for_vat" name="exch_rate_for_vat" style="text-align: right;"
                                                        placeholder="Enter Exchange Rate" onchange="formatAmount($(this));"
                                                        value="@if(old('exch_rate_for_vat')){{ old('exch_rate_for_vat') }}@endif">
                                                    @error('exch_rate_for_vat')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div> --}}
                                            <div class="row mb-3">
                                                <label for="bea_masuk_val" class="col-sm-3 col-form-label">Bea Masuk Import</label>
                                                <div class="col-sm-9">
                                                    <input {{ $readonly }} type="text" class="form-control @error('bea_masuk_val') is-invalid @enderror"
                                                        maxlength="255" id="bea_masuk_val" name="bea_masuk_val" style="text-align: right;"
                                                        placeholder="Enter Bea Masuk Import" onchange="formatAmount($(this));"
                                                        value="@if(old('bea_masuk_val')){{ old('bea_masuk_val') }}@endif">
                                                    @error('bea_masuk_val')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label id="import_shipping_cost_val_lbl" for="import_shipping_cost_val" class="col-sm-3 col-form-label">
                                                    Import Shipping Cost{{ $currency_code!=""? " (".$currency_code.")": "" }}                                                    
                                                </label>
                                                <div class="col-sm-9">
                                                    <input {{ $readonly }} type="text" class="form-control @error('import_shipping_cost_val') is-invalid @enderror"
                                                        maxlength="255" id="import_shipping_cost_val" name="import_shipping_cost_val" style="text-align: right;"
                                                        placeholder="Enter Import Shipping Cost" onchange="formatAmount($(this));"
                                                        value="@if(old('import_shipping_cost_val')){{ old('import_shipping_cost_val') }}@endif">
                                                    @error('import_shipping_cost_val')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="bl_no" class="col-sm-3 col-form-label">B/L No*</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('bl_no') is-invalid @enderror"
                                                        maxlength="255" id="bl_no" name="bl_no"
                                                        placeholder="Enter B/L Number"
                                                        value="@if (old('bl_no')){{ old('bl_no') }}@endif">
                                                    @error('bl_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="vessel_no" class="col-sm-3 col-form-label">Vessel No</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('vessel_no') is-invalid @enderror"
                                                        maxlength="255" id="vessel_no" name="vessel_no"
                                                        placeholder="Enter Vessel Number"
                                                        value="@if (old('vessel_no')){{ old('vessel_no') }}@endif">
                                                    @error('vessel_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="weight_type_id01" class="col-sm-3 col-form-label">Weight Type</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('weight_type_id01') is-invalid @enderror" id="weight_type_id01" name="weight_type_id01">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $weight_type_id01 = (old('weight_type_id01')?old('weight_type_id01'):0);
                                                        @endphp
                                                        @foreach ($weighttype as $wT)
                                                            <option @if ($weight_type_id01==$wT->id) {{ 'selected' }} @endif value="{{ $wT->id }}">{{ $wT->title_ind }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('weight_type_id01')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="weight_type_id02" class="col-sm-3 col-form-label">Dimension</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('weight_type_id02') is-invalid @enderror" id="weight_type_id02" name="weight_type_id02">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $weight_type_id02 = (old('weight_type_id02')?old('weight_type_id02'):0);
                                                        @endphp
                                                        @foreach ($weighttype as $wT)
                                                            <option @if ($weight_type_id02==$wT->id) {{ 'selected' }} @endif value="{{ $wT->id }}">{{ $wT->title_ind }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('weight_type_id02')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="po_pm_no" class="col-sm-3 col-form-label">PO/MO No*</label>
                                                <div class="col-sm-5">
                                                    <select class="form-select single-select @error('po_pm_no') is-invalid @enderror"
                                                        id="po_pm_no" name="po_pm_no" onchange="dispPoCurr(this.value);">
                                                        <option value="">Choose...</option>
                                                        @php
                                                            $po_pm_no = (old('po_pm_no')?old('po_pm_no'):0);
                                                        @endphp
                                                        @foreach ($get_po_pm_no as $wT)
                                                            @if ($wT->memo_po_qty!=$wT->memo_po_ro_qty)
                                                                <option @if($po_pm_no==$wT->order_no){{ 'selected' }}@endif
                                                                    value="{{ $wT->order_no }}">{{ $wT->order_no.' ('.($wT->is_vat=='Y'?'VAT':'Non VAT').')' }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('po_pm_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <input type="hidden" name="po_pm_no_all" id="po_pm_no_all" value="@if(old('po_pm_no_all')){{ old('po_pm_no_all') }}@endif">
                                                <input type="button" name="gen_part" id="gen_part" class="btn btn-primary px-5 col-sm-4" value="Generate Part">
                                                <input type="hidden" name="last_is_vat" id="last_is_vat" value="@if(old('last_is_vat')){{ old('last_is_vat') }}@endif">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-5">
                                                    <table class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 95%;">PO/MO No</th>
                                                                <th scope="col" style="width: 2%;">Del</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-po-mo">
                                                            @if (old('po_pm_no_all'))
                                                                @php
                                                                    $po_mo_no_arr = explode(",",old('po_pm_no_all'));
                                                                    $iRow = 0;
                                                                @endphp
                                                                @for ($i=0;$i<count($po_mo_no_arr);$i++)
                                                                    @if ($po_mo_no_arr[$i]!='')
                                                                        <tr id="row_po_mo_{{ $i }}">
                                                                            <th scope="row" style="text-align:right;"><label id="po_mo_row_number{{ $iRow }}" for="" class="col-form-label">{{ $iRow+1 }}.</label></th>
                                                                            <td scope="row" style="text-align:left;">
                                                                                <label for="" id="s_po_mo_no{{ $i }}" class="col-form-label">{{ $po_mo_no_arr[$i] }}</label>
                                                                            </td>
                                                                            <td style="text-align:center;">
                                                                                <input type="checkbox" id="rowPoMoCheck{{ $i }}" value="{{ $i }}">
                                                                            </td>
                                                                        </tr>
                                                                        @php
                                                                            $iRow++;
                                                                        @endphp
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                    <input type="hidden" name="totalRowPoMo" id="totalRowPoMo" value="@if (old('totalRowPoMo')){{ old('totalRowPoMo') }}@endif">
                                                    <input type="button" name="rm_po_mo" id="rm_po_mo" class="btn btn-danger px-5" value="Remove Row" style="margin-top: 10px;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="journal_type_id" class="col-sm-3 col-form-label">Journal Type</label>
                                                <div class="col-sm-6">
                                                    <select class="form-select single-select @error('journal_type_id') is-invalid @enderror" id="journal_type_id" name="journal_type_id">
                                                        <option value="">Choose...</option>
                                                        @php
                                                            $journal_type_id = (old('journal_type_id')?old('journal_type_id'):'');
                                                        @endphp
                                                        @foreach ($journal_type as $t)
                                                            <option @if($journal_type_id==$t){{ 'selected' }}@endif value="{{ $t }}">{{ $t }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('journal_type_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <label for="journal_type_id" id="journal_type_info" class="col-sm-3 col-form-label" style="color: red;font-weight:700;">
                                                    @if ($journal_type_id=='P'){{ 'Akan Dibayar PPN?' }}@endif
                                                    @if ($journal_type_id=='N'){{ 'Akan Dibayar Non PPN?' }}@endif
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="currency_name" class="col-sm-3 col-form-label">Currency</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="currency_id" id="currency_id" value="@if(old('currency_id')){{ old('currency_id') }}@endif">
                                                    <input readonly type="text" class="form-control @error('currency_name') is-invalid @enderror"
                                                        maxlength="255" id="currency_name" name="currency_name"
                                                        value="@if (old('currency_name')){{ old('currency_name') }}@endif">
                                                    @error('currency_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="exc_rate_inv_amount" class="col-sm-3 col-form-label">Rp Amount</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('exc_rate_inv_amount') is-invalid @enderror"
                                                        maxlength="255" id="exc_rate_inv_amount" name="exc_rate_inv_amount" style="text-align: right;"
                                                        value="@if (old('exc_rate_inv_amount')){{ old('exc_rate_inv_amount') }}@endif">
                                                    @error('exc_rate_inv_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="courier_id" class="col-sm-3 col-form-label">Ship By</label>
                                                <div class="col-sm-3">
                                                    <select class="form-select" name="courier_type" id="courier_type">
                                                        <option @if(old('courier_type')==env('AMBIL_SENDIRI')){{ 'selected' }}@endif value="{{ env('AMBIL_SENDIRI') }}">{{ env('AMBIL_SENDIRI_STR') }}</option>
                                                        <option @if(old('courier_type')==env('DIANTAR')){{ 'selected' }}@endif value="{{ env('DIANTAR') }}">{{ env('DIANTAR_STR') }}</option>
                                                        <option @if(old('courier_type')==env('COURIER')){{ 'selected' }}@endif value="{{ env('COURIER') }}">{{ env('COURIER_STR') }}</option>
                                                    </select>
                                                </div>
                                                <div id="courier-list" class="col-sm-6" style="@if(old('courier_type')==env('COURIER')){{ 'display: block;' }}@else{{ 'display: none;' }}@endif">
                                                    <select class="form-select single-select @error('courier_id') is-invalid @enderror" id="courier_id" name="courier_id">
                                                        <option value="">Choose...</option>
                                                        @php
                                                            $p_Id = (old('courier_id')?old('courier_id'):0);
                                                        @endphp
                                                        @foreach ($couriers as $c)
                                                            <option @if ($p_Id==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('courier_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="shipto_name" class="col-sm-3 col-form-label">Ship To</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="shipto_id" id="shipto_id" value="@if(old('shipto_id')){{ old('shipto_id') }}@endif">
                                                    <input readonly type="text" class="form-control @error('shipto_name') is-invalid @enderror"
                                                        maxlength="255" id="shipto_name" name="shipto_name"
                                                        value="@if (old('shipto_name')){{ old('shipto_name') }}@endif">
                                                    @error('shipto_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="gross_weight" class="col-sm-3 col-form-label">Gross Weight</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('gross_weight') is-invalid @enderror" onkeyup="formatAmount($(this));"
                                                        maxlength="255" id="gross_weight" name="gross_weight" style="text-align: right;"
                                                        value="@if (old('gross_weight')){{ old('gross_weight') }}@endif">
                                                    @error('gross_weight')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="measurement" class="col-sm-3 col-form-label">Measurement</label>
                                                {{-- onkeyup="formatAmount($(this));" --}}
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('measurement') is-invalid @enderror" maxlength="255" id="measurement" name="measurement" 
                                                        style="text-align: right;" value="@if (old('measurement')){{ old('measurement') }}@endif">
                                                    @error('measurement')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@endif</textarea>
                                                    @error('remark')
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
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 15%;">Price FOB</th>
                                        <th scope="col" style="width: 15%;">Total FOB</th>
                                        <th scope="col" style="width: 15%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 2%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $iRowPart = 1;
                                        $lastTotalAmount = 0;
                                        $lastTotalFobAmount = 0;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for ($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if (old('part_id'.$lastIdx))
                                                <tr id="row{{ $lastIdx }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="receipt_order_row_number{{ $lastIdx }}" class="col-form-label">{{ $iRowPart }}.</label>
                                                    </th>
                                                    <td>
                                                        @php
                                                            $qParts = \App\Models\Mst_part::where('id','=',old('part_id'.$lastIdx))
                                                            ->first();

                                                            $partNumber = $qParts->part_number;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}"
                                                            class="col-form-label">{{ $partNumber.' : '.$qParts->part_name }}</label>
                                                        <input type="hidden" name="po_mo_no{{ $lastIdx }}" id="po_mo_no{{ $lastIdx }}" value="{{ old('po_mo_no'.$lastIdx) }}">
                                                        <input type="hidden" name="po_mo_id_{{ $lastIdx }}" id="po_mo_id_{{ $lastIdx }}" value="{{ old('po_mo_id_'.$lastIdx) }}">
                                                        <input type="hidden" name="po_mo_part_id_{{ $lastIdx }}" id="po_mo_part_id_{{ $lastIdx }}" value="{{ old('po_mo_part_id_'.$lastIdx) }}">
                                                        <input type="hidden" name="part_id{{ $lastIdx }}" id="part_id{{ $lastIdx }}" value="{{ old('part_id'.$lastIdx) }}">
                                                    </td>
                                                    <td>
                                                        <input onchange="calcGrandTotal();" type="text" name="qty{{ $lastIdx }}" id="qty{{ $lastIdx }}"
                                                            class="form-control @error('qty'.$lastIdx) is-invalid @enderror" style="text-align: right;width: 100%;"
                                                            value="{{ old('qty'.$lastIdx) }}">
                                                        <input type="hidden" name="qty_on_po{{ $lastIdx }}" id="qty_on_po{{ $lastIdx }}" value="{{ old('qty_on_po'.$lastIdx) }}">
                                                        @error('qty'.$lastIdx)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    @php
                                                        $price_fob = 0;
                                                        $price_local = 0;
                                                        $currency_fob_tmp = old('currency_fob_tmp')?old('currency_fob_tmp'):'';
                                                        // $exch_rate_for_vat = 0;
                                                        $vat_import = 0;
                                                    @endphp
                                                    @if ($qSupplier->supplier_type_id==10)
                                                        {{-- international --}}
                                                        @php
                                                            $exc_rate = old('exc_rate')==''?0:str_replace(",","",old('exc_rate'));
                                                            $vat_import = old('vat_import')==''?0:str_replace(",","",old('vat_import'));
                                                            // $exch_rate_for_vat = old('exch_rate_for_vat')==''?0:str_replace(",","",old('exch_rate_for_vat'));
                                                            $price_fob = number_format(old('price_fob_val'.$lastIdx),2,'.',',');
                                                            $lastTotalFobAmount += (old('qty'.$lastIdx)*old('price_fob_val'.$lastIdx));
                                                            $price_local = number_format(old('price_fob_val'.$lastIdx)*$exc_rate,0,'.',',');
                                                            $total = number_format(old('qty'.$lastIdx)*old('price_fob_val'.$lastIdx)*$exc_rate,0,'.',',');
                                                            $lastTotalAmount+= (old('qty'.$lastIdx)*old('price_fob_val'.$lastIdx)*$exc_rate);
                                                        @endphp
                                                    @endif
                                                    @if ($qSupplier->supplier_type_id==11)
                                                        {{-- lokal --}}
                                                        @php
                                                            $price_fob = 0;
                                                            $price_local = number_format(old('price_local_val'.$lastIdx),0,'.',',');
                                                            $total = number_format(old('qty'.$lastIdx)*old('price_local_val'.$lastIdx),0,'.',',');

                                                            $lastTotalFobAmount = 0;
                                                            $lastTotalAmount+= (old('qty'.$lastIdx)*old('price_local_val'.$lastIdx));
                                                        @endphp
                                                    @endif
                                                    <td style="text-align: right;">
                                                        <input type="hidden" name="price_fob_val{{ $lastIdx }}" id="price_fob_val{{ $lastIdx }}" value="{{ old('price_fob_val'.$lastIdx) }}">
                                                        <label for="" name="price_fob{{ $lastIdx }}" id="price_fob{{ $lastIdx }}" class="col-form-label">
                                                            {{ (old('supplier_type_id')==10?$currency_fob_tmp:'').number_format(old('price_fob_val'.$lastIdx),(old('supplier_type_id')==10?2:0),'.',',') }}
                                                        </label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" name="total_fob{{ $lastIdx }}" id="total_fob{{ $lastIdx }}" class="col-form-label">
                                                            {{ (old('supplier_type_id')==10?$currency_fob_tmp:'').number_format(old('qty'.$lastIdx)*old('price_fob_val'.$lastIdx),(old('supplier_type_id')==10?2:0),'.',',') }}
                                                        </label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <input type="hidden" name="price_local_val{{ $lastIdx }}" id="price_local_val{{ $lastIdx }}" value="{{ old('price_local_val'.$lastIdx) }}">
                                                        <label for="" name="price_local{{ $lastIdx }}" id="price_local{{ $lastIdx }}" class="col-form-label">{{ $price_local }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}" class="col-form-label">{{ $total }}</label>
                                                    </td>
                                                    <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $lastIdx }}" value="{{ $lastIdx }}"></td>
                                                </tr>
                                                @php
                                                    $iRowPart++;
                                                @endphp
                                            @endif
                                        @endfor

                                        <tr id="rowTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalFOBAmount" id="lblTotalFOBAmount" class="col-form-label">
                                                    {{ (old('supplier_type_id')==10?$currency_fob_tmp:'').number_format($lastTotalFobAmount,(old('supplier_type_id')==10?2:0),'.',',') }}
                                                </label>
                                            </td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalAmount" id="lblTotalAmount"
                                                    class="col-form-label">{{ number_format($lastTotalAmount,0,'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        @php
                                            $bea_masuk = 0;
                                            $import_shipping_cost = 0;
                                        @endphp
                                        @if (old('bea_masuk_val'))
                                            @php
                                                $bea_masuk = str_replace(",","",old('bea_masuk_val'));
                                            @endphp
                                        @endif
                                        @if (old('import_shipping_cost_val'))
                                            @php
                                                $import_shipping_cost = str_replace(",","",old('import_shipping_cost_val'));
                                            @endphp
                                        @endif
                                        {{-- <tr id="rowBeaMasuk">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblBeaMasuk" id="lblBeaMasuk" class="col-form-label">Bea Masuk Import</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblBeaMasukFOBAmount" id="lblBeaMasukFOBAmount"
                                                    class="col-form-label">{{ $currency_fob_tmp.number_format(($bea_masuk>0?$bea_masuk/$exc_rate:0),(old('supplier_type_id')==10?2:0),'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblBeaMasukAmount" id="lblBeaMasukAmount"
                                                    class="col-form-label">{{ number_format($bea_masuk,0,'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr> --}}
                                        <tr id="rowVAT">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblVATFOBAmount" id="lblVATFOBAmount" class="col-form-label">&nbsp;</label>
                                            </td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">
                                                    @if ($qSupplier->supplier_type_id==10)
                                                        {{ (old('last_is_vat')=='Y'?number_format($vat_import,0,'.',','):0) }}
                                                        {{-- {{ (old('last_is_vat')=='Y'?number_format(((($lastTotalFobAmount+$import_shipping_cost)*$exch_rate_for_vat)+$bea_masuk)*$vat/100,0,'.',','):0) }} --}}
                                                    @endif
                                                    @if ($qSupplier->supplier_type_id==11)
                                                        {{ (old('last_is_vat')=='Y'?number_format(($lastTotalAmount+$bea_masuk)*$vat/100,0,'.',','):0) }}
                                                    @endif
                                                </label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr id="rowGrandTotal">
                                            <td colspan="4" style="text-align: right;">
                                                <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblGrandTotalFOBAmount" id="lblGrandTotalFOBAmount" class="col-form-label">&nbsp;</label>
                                            </td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">
                                                    @if ($qSupplier->supplier_type_id==10)
                                                        {{ (old('last_is_vat')=='Y'?number_format($lastTotalAmount+$vat_import,0,'.',','):0) }}
                                                        {{-- {{ (old('last_is_vat')=='Y'?number_format($lastTotalAmount+(((($lastTotalFobAmount+$import_shipping_cost)*$exch_rate_for_vat)+$bea_masuk)*$vat/100),0,'.',','):0) }} --}}
                                                    @endif
                                                    @if ($qSupplier->supplier_type_id==11)
                                                        {{ number_format($lastTotalAmount+(old('last_is_vat')=='Y'?($lastTotalAmount*$vat/100):0),0,'.',',') }}
                                                    @endif
                                                </label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="lastTotalAmountTmp" id="lastTotalAmountTmp" value="{{ ($lastTotalFobAmount>0)?$lastTotalFobAmount:$lastTotalAmount }}">
                <input type="hidden" name="currency_fob_tmp" id="currency_fob_tmp" value="@if(old('currency_fob_tmp')){{ old('currency_fob_tmp') }}@endif">
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
    function formatAmount(elm){
        let validateChars = '0123456789,.';
        let amount = (elm.val()==''?0:elm.val().replaceAll(',',''));
        for(let i=0;i<amount.length;i++){
            if (validateChars.indexOf(amount.substr(i, 1))==-1){
                elm.val(0);
                return false;
            }
        }

        if($('#supplier_type_id').val()==10){
            amount = parseFloat(amount).numberFormat(2,'.',',');
        }else{
            amount = parseFloat(amount).numberFormat(0,'.',',');
        }
        elm.val(amount);

        // set cursor position
        if($('#supplier_type_id').val()==10){
            if(elm.val().length>=3){
                elm.selectRange(elm.val().length-3); // set cursor position
            }
        }
    }

    function dispPoPm(supplier_id){
        if(supplier_id===''){
            if($("#supplier_id").val()==='#'){return false;}
            supplier_id = $("#supplier_id").val();
        }

        var fd = new FormData();
        fd.append('id', supplier_id);
        $.ajax({
            url: "{{ url('/disp_supplierinfo_by_id') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].suppliers;
                $('#supplier_type_id').val(o.supplier_type_id);
            },
        });

        let branch_id = '';
        @if ($userLogin->is_director=='Y')
            if($("#branch_id").val()==='#'){
                alert('Please select a valid branch');
                location.href = '{!! url()->current() !!}';
                return false;
            }else{
                branch_id = $("#branch_id").val();
            }
        @else
            branch_id = {{ $userLogin->branch_id }};
        @endif
        $("#po_pm_no").empty();
        $("#po_pm_no").append(`<option value="#">Choose...</option>`);
        $("#new-row").empty();
        $("#new-row-po-mo").empty();
        $("#po_pm_no_all").val('');
        $("#totalRow").val(0);
        $("#totalRowPoMo").val(0);
        $("#lastTotalAmountTmp").val(0);
        $("#currency_id").val('');
        $("#currency_name").val('');
        $("#shipto_id").val('');
        $("#shipto_name").val('');
        $("#currency_fob_tmp").val('');

        var fd = new FormData();
        fd.append('supplier_id', supplier_id);
        fd.append('branch_id', branch_id);
        $.ajax({
            url: "{{ url('/disp_po_pm') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].po_pm;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        let memo_po_qty = 0;
                        if(o[i].memo_po_qty!==null){
                            memo_po_qty = o[i].memo_po_qty;
                        }

                        let memo_po_ro_qty = 0;
                        if(o[i].memo_po_ro_qty!==null){
                            memo_po_ro_qty = o[i].memo_po_ro_qty;
                        }

                        if(parseInt(memo_po_qty) !== parseInt(memo_po_ro_qty)){
                            let vat = ' (Non VAT)';
                            if (o[i].is_vat=='Y'){vat = ' (VAT)';}
                            optionText = o[i].order_no+vat;
                            optionValue = o[i].order_no;
                            $("#po_pm_no").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                }
            }
        });
    }

    function dispPoCurr(po_no){
        $("#currency_id").val('');
        $("#currency_name").val('');
        $("#shipto_id").val('');
        $("#shipto_name").val('');

        var fd = new FormData();
        fd.append('po_no', po_no);
        $.ajax({
            url: "{{ url('/disp_po_curr') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].curr;
                $("#currency_id").val(o.curr_id);
                $("#currency_name").val(o.title_ind);
                $("#currency_fob_tmp").val(o.currency_symbol);
                $("#shipto_id").val(o.shipto_id);
                $("#shipto_name").val(o.shipto_name);
            }
        });
    }

    function dispPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml =
            '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;"><label for="" id="receipt_order_row_number'+totalRow+'" class="col-form-label">'+rowNo+'.</label></th>'+
            '<td>'+
            '<input type="hidden" name="po_mo_no'+totalRow+'" id="po_mo_no'+totalRow+'">'+
            '<input type="hidden" name="po_mo_id_'+totalRow+'" id="po_mo_id_'+totalRow+'">'+
            '<input type="hidden" name="po_mo_part_id_'+totalRow+'" id="po_mo_part_id_'+totalRow+'">'+
            '<input type="hidden" name="part_id'+totalRow+'" id="part_id'+totalRow+'">'+
            '<label for="" name="part_name'+totalRow+'" id="part_name'+totalRow+'" class="col-form-label"></label>'+
            '</td>'+
            '<td style="text-align: right;">'+
            '<input onchange="calcGrandTotal();" type="text" name="qty'+totalRow+'" id="qty'+totalRow+'" '+
            'class="form-control" style="text-align: right;width:100%;">'+
            '<input type="hidden" name="qty_on_po'+totalRow+'" id="qty_on_po'+totalRow+'">'+
            '</td>'+
            '<td style="text-align: right;">'+
            '<input type="hidden" name="price_fob_val'+totalRow+'" id="price_fob_val'+totalRow+'">'+
            '<label for="" name="price_fob'+totalRow+'" id="price_fob'+totalRow+'" class="col-form-label"></label>'+
            '</td>'+
            '<td style="text-align: right;"><label for="" name="total_fob'+totalRow+'" id="total_fob'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align: right;">'+
            '<input type="hidden" name="price_local_val'+totalRow+'" id="price_local_val'+totalRow+'">'+
            '<label for="" name="price_local'+totalRow+'" id="price_local'+totalRow+'" class="col-form-label"></label>'+
            '</td>'+
            '<td style="text-align: right;"><label for="" name="total'+totalRow+'" id="total'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);
        calcGrandTotal();

        // reset penomoran - start - detil part
        let j = 1;
        for (i = 0; i < $("#totalRow").val(); i++) {
            if($("#receipt_order_row_number"+i).text()){
                $("#receipt_order_row_number"+i).text(j+'. ');
                j++;
            }
        }
        // reset penomoran - end - detil part
    }

    function dispPoMo(sPoMo){
        let totalRowPoMo = $("#totalRowPoMo").val();
        if(isNaN(totalRowPoMo) || totalRowPoMo===''){
            totalRowPoMo = 0;
        }

        for (i = 0; i <= totalRowPoMo; i++) {
            let po_mo_no = $("#s_po_mo_no"+(i)).text();
            if (po_mo_no==sPoMo){
                return false;
            }
        }

        let rowPoMoNo = parseInt(totalRowPoMo)+1;
        let vHtml = '<tr id="row_po_mo_'+totalRowPoMo+'">'+
            '<th scope="row" style="text-align:right;"><label id="po_mo_row_number'+(rowPoMoNo-1)+'" for="" class="col-form-label">'+rowPoMoNo+'.</label></th>'+
            '<td scope="row" style="text-align:left;"><label for="" id="s_po_mo_no'+totalRowPoMo+'" class="col-form-label">'+sPoMo+'</label></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowPoMoCheck'+totalRowPoMo+'" value="'+totalRowPoMo+'"></td>'+
            '</tr>';
        $("#new-row-po-mo").append(vHtml);
        $("#totalRowPoMo").val(rowPoMoNo);

        // reset penomoran - start - po/mo
        let j = 1;
        for (i = 0; i < $("#totalRowPoMo").val(); i++) {
            if($("#po_mo_row_number"+i).text()){
                $("#po_mo_row_number"+i).text(j+'. ');
                j++;
            }
        }
        // reset penomoran - end - po/mo
    }

    function calcGrandTotal(){
        let totalAmount = 0;
        let beamasukAmount = ($("#bea_masuk_val").val()!==''?$("#bea_masuk_val").val().replaceAll(',',''):0);
        let shippingCostAmount = ($("#import_shipping_cost_val").val()!==''?$("#import_shipping_cost_val").val().replaceAll(',',''):0);
        let totalFobAmount = 0;
        let exc_rate = $("#exc_rate").val().replaceAll(',','');
        let vat_import = $("#vat_import").val().replaceAll(',','');
        // let exch_rate_for_vat = $("#exch_rate_for_vat").val().replaceAll(',','');
        let supplier_type_id = $("#supplier_type_id").val();
        let digitAfterComma = 0;
        if (supplier_type_id==10){
            digitAfterComma = 2;
        }

        let excRate = $("#exc_rate").val().replaceAll(',','');
        if (isNaN(excRate)){excRate = 0;}
        if (supplier_type_id==10){
            digitAfterComma = 2;
        }

        for(let i=0;i<$("#totalRow").val();i++){
            if($("#price_fob_val"+i).val()){
                let price_fob = $("#price_fob_val"+i).val();
                let price_local = $("#price_local_val"+i).val();

                if(!isNaN(exc_rate) && exc_rate!=='0' && exc_rate!==''){
                    price_local = price_fob*exc_rate;
                }

                let total_fob = price_fob*$("#qty"+i).val();
                $("#total_fob"+i).text(
                    (supplier_type_id==10?$("#currency_fob_tmp").val():'')+
                    parseFloat(total_fob).numberFormat(digitAfterComma,'.',',')
                );
                let total = price_local*$("#qty"+i).val();
                $("#price_local"+i).text(parseFloat(price_local).numberFormat(0,'.',','));
                $("#total"+i).text(parseFloat(total).numberFormat(0,'.',','));
            }
            if($("#total"+i).text()!==''){
                let totalTmp = $("#total"+i).text();
                totalTmp = totalTmp.replaceAll(',','').replaceAll('{{ $qCurrency->string_val }}','');

                totalAmount+= parseFloat(totalTmp);
            }
            if($("#total_fob"+i).text()!==''){
                // fob
                let totalFobTmp = $("#total_fob"+i).text();
                totalFobTmp = totalFobTmp.replaceAll(',','').replaceAll($("#currency_fob_tmp").val(),'');

                totalFobAmount+= parseFloat(totalFobTmp);
            }
        }

        if($("#currency_fob_tmp").val()==='' || $("#currency_fob_tmp").val().toLowerCase().indexOf("rp")>=0){
            $("#lastTotalAmountTmp").val(totalAmount);
        }else{
            $("#lastTotalAmountTmp").val(totalFobAmount);
        }

        $("#lblTotalAmount").text(totalAmount.numberFormat(0,'.',','));
        $("#lblTotalFOBAmount").text(
            (supplier_type_id==10?$("#currency_fob_tmp").val():'')+totalFobAmount.numberFormat(digitAfterComma,'.',',')
        );

        let vatAmount = ($('#last_is_vat').val()=='Y'?((totalAmount+parseFloat(beamasukAmount))*{{ $vat }}/100):0);
        if (supplier_type_id==10){
            // import
            vatAmount = ($('#last_is_vat').val()=='Y'?vat_import:0);
            // vatAmount = ($('#last_is_vat').val()=='Y'?((((totalFobAmount+parseFloat(shippingCostAmount))*exch_rate_for_vat)+parseFloat(beamasukAmount))*{{ $vat }}/100):0);
        }
        $("#lblVATAmount").text(
            $('#last_is_vat').val()=='Y'?parseFloat(vatAmount).numberFormat(0,'.',','):0
        );

        let vatFOBAmount = ($('#last_is_vat').val()=='Y'?vatAmount:0)/(vat_import>0?vat_import:1);
        // let vatFOBAmount = ($('#last_is_vat').val()=='Y'?vatAmount:0)/(exch_rate_for_vat>0?exch_rate_for_vat:1);
        // $("#lblVATFOBAmount").text(
        //     (supplier_type_id==10?$("#currency_fob_tmp").val():'')+
        //     ($('#last_is_vat').val()=='Y'?parseFloat(exch_rate_for_vat>1?vatFOBAmount:0).numberFormat(digitAfterComma,'.',','):0)
        // );

        let grandTotalAmount = parseFloat(totalAmount)+parseFloat(vatAmount);
        // if (supplier_type_id==10){
        //     // import
        //     grandTotalAmount = totalAmount+vatAmount;
        // }
        $("#lblGrandTotalAmount").text(
            parseFloat(grandTotalAmount).numberFormat(0,'.',',')
        );

        let grandTotalFOBamount = totalFobAmount+vatFOBAmount;
        // $("#lblGrandTotalFOBAmount").text(
        //     (supplier_type_id==10?$("#currency_fob_tmp").val():'')+parseFloat(grandTotalFOBamount).numberFormat(digitAfterComma,'.',',')
        // );
    }

    function dispRpAmount(){
        let supplier_type_id = {{ (old('supplier_type_id')?old('supplier_type_id'):0) }};
        let digitAfterComma = 0;
        if (supplier_type_id==10){digitAfterComma = 2;}

        let invoice_amount = $("#invoice_amount").val().replaceAll(',','');
        if (isNaN(invoice_amount)){return false;}

        let excRate = $("#exc_rate").val().replaceAll(',','');
        if (isNaN(excRate)){return false;}

        let vat_import = $("#vat_import").val().replaceAll(',','');
        if (isNaN(vat_import)){return false;}
        // let exch_rate_for_vat = $("#exch_rate_for_vat").val().replaceAll(',','');
        // if (isNaN(exch_rate_for_vat)){return false;}

        $("#exc_rate_inv_amount").val(parseFloat(excRate*invoice_amount).numberFormat(digitAfterComma,'.',','));

        let bea_masuk_val = $("#bea_masuk_val").val().replaceAll(',','');
        if (isNaN(bea_masuk_val)){return false;}

        let import_shipping_cost_val = $("#import_shipping_cost_val").val().replaceAll(',','');
        if (isNaN(import_shipping_cost_val)){return false;}

        // $("#lblBeaMasukFOBAmount").text($("#currency_fob_tmp").val()+parseFloat(bea_masuk_val>0?(bea_masuk_val/excRate):0).numberFormat(digitAfterComma,'.',','));
        // $("#lblBeaMasukAmount").text(parseFloat((bea_masuk_val>0?bea_masuk_val:0)).numberFormat(0,'.',','));
    }

    $(document).ready(function() {
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
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            for (i = 0; i < $("#totalRowPoMo").val(); i++){
                if ($("#s_po_mo_no"+i).text()){
                    let po_mo_no_tmp = $("#s_po_mo_no"+i).text();
                    let po_mo_no_found = false;

                    for (let j = 0; j < $("#totalRow").val(); j++){
                        if($("#po_mo_no"+j).val()) {
                            if ($("#po_mo_no"+j).val()===$("#s_po_mo_no"+i).text()) {
                                po_mo_no_found = true;
                                break;
                            }
                        }
                    }
                    if (!po_mo_no_found){
                        // hapus no PO/MO jika sesuai
                        for (let iDel = 0; iDel < $("#totalRowPoMo").val(); iDel++) {
                            if ($("#s_po_mo_no"+iDel).text()) {
                                if ($("#s_po_mo_no"+iDel).text()===po_mo_no_tmp) {
                                    $("#row_po_mo_"+iDel).remove();
                                    let po_mo_no = $("#po_pm_no_all").val().replaceAll(po_mo_no_tmp,'');
                                    po_mo_no = po_mo_no.replaceAll(',,',',');
                                    $("#po_pm_no_all").val(po_mo_no);
                                }
                            }
                        }
                    }
                }
            }

            // reset penomoran - start - detil part
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#receipt_order_row_number"+i).text()){
                    $("#receipt_order_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end - detil part

            calcGrandTotal();

            // reset penomoran - start - po/mo
            j = 1;
            for (i = 0; i < $("#totalRowPoMo").val(); i++) {
                if($("#po_mo_row_number"+i).text()){
                    $("#po_mo_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end - po/mo
        });
        $("#rm_po_mo").click(function() {
            let po_mo_no = '';
            for (i = 0; i < $("#totalRowPoMo").val(); i++) {
                if ($("#rowPoMoCheck"+i).is(':checked')) {
                    for(let j=0;j<$("#totalRow").val();j++){
                        let po_mo_no_2del = $("#po_mo_no"+j).val();
                        if (po_mo_no_2del!==undefined){
                            if(po_mo_no_2del===$("#s_po_mo_no"+i).text()){
                                $("#row"+j).remove();

                                po_mo_no = $("#po_pm_no_all").val().replaceAll(po_mo_no_2del,'');
                                $("#po_pm_no_all").val(po_mo_no);
                            }
                        }
                    }

                    po_mo_no = $("#po_pm_no_all").val().replaceAll($("#s_po_mo_no"+i).text(),'');
                    $("#po_pm_no_all").val(po_mo_no);
                    $("#row_po_mo_"+i).remove();
                }
            }
            calcGrandTotal();

            // reset penomoran - start - po/mo
            let j = 1;
            for (i = 0; i < $("#totalRowPoMo").val(); i++) {
                if($("#po_mo_row_number"+i).text()){
                    $("#po_mo_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end - po/mo

            // reset penomoran - start - detil part
            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#receipt_order_row_number"+i).text()){
                    $("#receipt_order_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end - detil part
        });

        $("#exc_rate").change(function() {
            if ($("#exc_rate").val()=='NaN'){
                $("#exc_rate").val(0);
            }
            calcGrandTotal();
            dispRpAmount();
        });
        $("#vat_import").change(function() {
            if ($("#vat_import").val()=='NaN'){
                $("#vat_import").val(0);
            }
            calcGrandTotal();
            dispRpAmount();
        });
        // $("#exch_rate_for_vat").change(function() {
        //     if ($("#exch_rate_for_vat").val()=='NaN'){
        //         $("#exch_rate_for_vat").val(0);
        //     }
        //     calcGrandTotal();
        //     dispRpAmount();
        // });
        $("#bea_masuk_val").change(function() {
            if ($("#bea_masuk_val").val()=='NaN'){
                $("#bea_masuk_val").val(0);
            }
            calcGrandTotal();
            dispRpAmount();
        });

        $("#import_shipping_cost_val").change(function() {
            if ($("#import_shipping_cost_val").val()=='NaN'){
                $("#import_shipping_cost_val").val(0);
            }
            calcGrandTotal();
            dispRpAmount();
        });

        $("#invoice_amount").change(function() {
            dispRpAmount();
        });

        $("#journal_type_id").change(function() {
            if ($("#journal_type_id").val()==='P'){
                $("#journal_type_info").text('Akan Dibayar PPN?');
            }
            if ($("#journal_type_id").val()==='N'){
                $("#journal_type_info").text('Akan Dibayar Non PPN?');
            }
        });

        $("#courier_type").change(function() {
            if(parseInt($("#courier_type").val())===parseInt({{ env('COURIER') }})){
                $("#courier-list").css("display","block");
            }else{
                $("#courier-list").css("display","none");
            }
        });

        $("#supplier_id").change(function() {
            $("#currency_id").val('');
            $("#currency_name").val('');

            var fd = new FormData();
            fd.append('supplier_id', $('#supplier_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_supplier_pic') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].supplier_pic;
                    let c = res[0].supplier_curr;

                    let totOrder = o.length;
                    if (totOrder > 0) {
                        let supplier_type_id = 11;
                        for (let i = 0; i < totOrder; i++) {
                            supplier_type_id = o[i].supplier_type_id;
                        }
                        switch(supplier_type_id) {
                            case 10:
                                $("#exc_rate").removeAttr("readonly");
                                $("#vat_import").removeAttr("readonly");
                                // $("#exch_rate_for_vat").removeAttr("readonly");
                                $("#bea_masuk_val").removeAttr("readonly");
                                $("#vessel_no").removeAttr("readonly");

                                $("#currency_id").val(c.curr_id);
                                $("#currency_name").val(c.curr_name);
                                $("#invoice_amount_lbl").text("Invoice Amount ("+c.curr_code+")*");
                                $("#import_shipping_cost_val_lbl").text("Import Shipping Cost ("+c.curr_code+")");
                                break;
                            case 11:
                                $("#exc_rate").val('');
                                $("#exc_rate").attr("readonly","readonly");
                                $("#vat_import").val('');
                                $("#vat_import").attr("readonly","readonly");
                                // $("#exch_rate_for_vat").val('');
                                // $("#exch_rate_for_vat").attr("readonly","readonly");
                                $("#bea_masuk_val").val('');
                                $("#bea_masuk_val").attr("readonly","readonly");
                                $("#vessel_no").attr("readonly","readonly");
                                
                                $("#currency_id").val('');
                                $("#currency_name").val('');
                                $("#invoice_amount_lbl").text("Invoice Amount*");
                                $("#import_shipping_cost_val_lbl").text("Import Shipping Cost");
                                break;
                            default:
                                // default: lokal
                                $("#exc_rate").val('');
                                $("#exc_rate").attr("readonly");
                                $("#vat_import").val('');
                                $("#vat_import").attr("readonly","readonly");
                                // $("#exch_rate_for_vat").val('');
                                // $("#exch_rate_for_vat").attr("readonly","readonly");
                                $("#bea_masuk_val").val('');
                                $("#bea_masuk_val").attr("readonly","readonly");
                                $("#vessel_no").attr("readonly");

                                $("#currency_id").val('');
                                $("#currency_name").val('');
                                $("#invoice_amount_lbl").text("Invoice Amount*");
                                $("#import_shipping_cost_val_lbl").text("Import Shipping Cost");
                        }
                    }
                }
            });
        });

        $("#gen_part").click(function() {
            if($('#po_pm_no option:selected').val()=='#'){
                alert('Please select PO/MO No!');
                $('#po_pm_no').focus();
                return false;
            }
            if($("#po_pm_no_all").val().indexOf($('#po_pm_no option:selected').val())===-1){
                let excRate = $("#exc_rate").val().replaceAll(',','');
                if (isNaN(excRate)){
                    alert('Make sure the Exchange Rate is in numeric form!');
                    $("#exc_rate").val(0);
                    return false;
                }
                if(excRate===''){
                    $("#exc_rate").val(0);
                }
                let vat_import = $("#vat_import").val().replaceAll(',','');
                if (isNaN(vat_import)){
                    alert('Make sure the VAT Import is in numeric form!');
                    $("#vat_import").val(0);
                    return false;
                }
                if(vat_import===''){
                    $("#vat_import").val(0);
                }
                // let exch_rate_for_vat = $("#exch_rate_for_vat").val().replaceAll(',','');
                // if (isNaN(exch_rate_for_vat)){
                //     alert('Make sure the Exchange Rate is in numeric form!');
                //     $("#exch_rate_for_vat").val(0);
                //     return false;
                // }
                // if(exch_rate_for_vat===''){
                //     $("#exch_rate_for_vat").val(0);
                // }
                let bea_masuk_val = $("#bea_masuk_val").val().replaceAll(',','');
                if (isNaN(bea_masuk_val)){
                    alert('Make sure the Bea Masuk Import is in numeric form!');
                    $("#bea_masuk_val").val(0);
                    return false;
                }
                if(bea_masuk_val===''){
                    $("#bea_masuk_val").val(0);
                }

                // agar no order tidak duplikat
                let orderNo = $("#po_pm_no_all").val()+','+$('#po_pm_no option:selected').val();
                $("#po_pm_no_all").val(orderNo);

                let currency_fob = '';
                var fd = new FormData();
                fd.append('po_or_mo', $('#po_pm_no option:selected').val());
                fd.append('supplier_id', $('#supplier_id option:selected').val());
                $.ajax({
                    url: "{{ url('/disp_po_pm_part') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let s = res[0].supplier;
                        let o = res[0].po_pm;
                        let totOrder = o.length;
                        if (totOrder > 0) {
                            let TotalAmount = 0;
                            let beamasukAmount = ($("#bea_masuk_val").val()!==''?$("#bea_masuk_val").val().replaceAll(',',''):0);
                            let TotalFOBAmount = 0;
                            let setPoMo = false;
                            for (let i = 0; i < totOrder; i++) {
                                if (o[i].is_vat!=$('#last_is_vat').val() && $('#last_is_vat').val()!=''){
                                    if ($('#last_is_vat').val()=='Y'){
                                        alert('Make sure you choose a Purchase Order number or Purchase Memo number that uses VAT.');
                                    }
                                    if ($('#last_is_vat').val()=='N'){
                                        alert('Make sure you choose a Purchase Order number or Purchase Memo number that does not use VAT.');
                                    }

                                    let po_mo = $("#po_pm_no_all").val();
                                    let result = po_mo.replace(','+$('#po_pm_no option:selected').val(), "");
                                    $("#po_pm_no_all").val(result);

                                    return false;
                                }else{
                                    $("#rowTotal").remove();
                                    $("#rowVAT").remove();
                                    $("#rowGrandTotal").remove();
                                }

                                dispPoMo($('#po_pm_no option:selected').val());
                                $('#last_is_vat').val(o[i].is_vat);

                                $("#journal_type_id option[value='"+(o[i].is_vat=='Y'?'P':'N')+"']").prop("selected", true).change();
                                if ((o[i].is_vat=='Y'?'P':'N')=='P'){$("#journal_type_info").text('Akan Dibayar PPN?');}
                                if ((o[i].is_vat=='Y'?'P':'N')=='N'){$("#journal_type_info").text('Akan Dibayar Non PPN?');}

                                let last_qty_total = o[i].last_qty_total;
                                if(o[i].last_qty_total===null){
                                    last_qty_total = 0;
                                }
                                let qty = 0;
                                let qty_on_po = 0;
                                if(parseInt(o[i].qty)-parseInt(last_qty_total)>0){
                                    qty = parseInt(o[i].qty)-parseInt(last_qty_total);
                                    qty_on_po = parseInt(o[i].qty)-parseInt(last_qty_total);
                                }
                                if(qty>0){
                                    setPoMo = true;
                                    dispPart();

                                    let totalRow = $("#totalRow").val();
                                    let part_no_tmp = o[i].part_number;
                                    if(part_no_tmp.length<11){
                                        part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, part_no_tmp.length);
                                    }else{
                                        part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, 10)+"-"+part_no_tmp.substring(10, part_no_tmp.length);
                                    }
                                    $("#part_name"+(totalRow-1)).text(part_no_tmp+' : '+o[i].part_name);
                                    $("#qty"+(totalRow-1)).val(qty);
                                    $("#qty_on_po"+(totalRow-1)).val(qty_on_po);
                                    // console.log('qty: '+qty);
                                    // console.log('qty_on_po: '+qty_on_po);
                                    $("#part_id"+(totalRow-1)).val(o[i].part_id);
                                    $("#po_mo_no"+(totalRow-1)).val(o[i].po_mo_no);
                                    $("#po_mo_id_"+(totalRow-1)).val(o[i].pomo_part_id);

                                    if(s.supplier_type_id==10){
                                        // internasional
                                        let currency_name = o[i].currency_name;
                                        if(typeof currency_name === 'undefined'){
                                            currency_name = '';
                                        }
                                        currency_fob = currency_name;
                                        $("#price_fob"+(totalRow-1)).text(currency_name+parseFloat(o[i].price).numberFormat(2,'.',','));
                                        $("#price_fob_val"+(totalRow-1)).val(o[i].price);
                                        $("#total_fob"+(totalRow-1)).text(currency_name+parseFloat($("#price_fob_val"+(totalRow-1)).val()*$("#qty"+(totalRow-1)).val()).numberFormat(2,'.',','));
                                        $("#price_local"+(totalRow-1)).text(parseFloat(o[i].price*excRate).numberFormat(0,'.',','));
                                        $("#price_local_val"+(totalRow-1)).val((o[i].price*excRate).numberFormat(0,'.',','));
                                        $("#total"+(totalRow-1)).text(parseFloat(qty*o[i].price*excRate).numberFormat(0,'.',','));

                                        TotalAmount+= qty*o[i].price*excRate;
                                        TotalFOBAmount+= qty*o[i].price;
                                    }
                                    if(s.supplier_type_id==11){
                                        // lokal
                                        let currency_name = o[i].currency_name;
                                        if(typeof currency_name === 'undefined'){
                                            currency_name = '';
                                        }
                                        currency_fob = currency_name;
                                        $("#price_fob"+(totalRow-1)).text(0);
                                        $("#price_fob_val"+(totalRow-1)).val(0);
                                        $("#total_fob"+(totalRow-1)).text(0);
                                        $("#price_local"+(totalRow-1)).text(parseFloat(o[i].price).numberFormat(0,'.',','));
                                        $("#price_local_val"+(totalRow-1)).val(o[i].price);
                                        $("#total"+(totalRow-1)).text((qty*o[i].price).numberFormat(0,'.',','));

                                        TotalAmount+= qty*o[i].price;
                                    }
                                }
                            }
                            if(!setPoMo){
                                let po_mo = $("#po_pm_no_all").val();
                                let result = po_mo.replace(','+$('#po_pm_no option:selected').val(), "");
                                $("#po_pm_no_all").val(result);
                            }

                            let lastTotalAmount = parseFloat(TotalAmount).numberFormat(0,'.',',');
                            let lastTotalFOBAmount = parseFloat(TotalFOBAmount).numberFormat((s.supplier_type_id==10?2:0),'.',',');
                            let lastBeaMasukFOBAmount = parseFloat(beamasukAmount>0?beamasukAmount/excRate:0).numberFormat(0,'.',',');
                            let lastVAT = ($('#last_is_vat').val()=='Y'?parseFloat((TotalAmount+beamasukAmount)*{{ $vat }}/100).numberFormat(0,'.',','):0);
                            let lastVATFOB = ($('#last_is_vat').val()=='Y'?parseFloat(vat_import).numberFormat(0,'.',','):0);
                            // let lastVATFOB = ($('#last_is_vat').val()=='Y'?
                            //     parseFloat(((TotalAmount+(beamasukAmount>0?beamasukAmount:0))*{{ $vat }}/100)/exch_rate_for_vat).numberFormat(0,'.',','):0);
                            let grandTotal = ((parseFloat(TotalAmount+(beamasukAmount>0?beamasukAmount:0))+($('#last_is_vat').val()=='Y'?
                                parseFloat((TotalAmount+(beamasukAmount>0?beamasukAmount:0))*{{ $vat }}/100):0)).numberFormat(0,'.',','));
                            let grandTotalFOB = (parseFloat(TotalAmount+(beamasukAmount>0?beamasukAmount:0))+($('#last_is_vat').val()=='Y'?parseFloat(vat_import):0)).numberFormat(0,'.',',');
                            // let grandTotalFOB = (parseFloat(TotalAmount+(beamasukAmount>0?beamasukAmount:0))+
                            //     ($('#last_is_vat').val()=='Y'?parseFloat(((TotalAmount+(beamasukAmount>0?beamasukAmount:0))*{{ $vat }}/100)/exch_rate_for_vat):0)).numberFormat(0,'.',',');
                            if(s.supplier_type_id==10){
                                // internasional
                                $("#lastTotalAmountTmp").val(TotalFOBAmount);

                                lastVAT = ($('#last_is_vat').val()=='Y'?parseFloat(vat_import).numberFormat(0,'.',','):0);
                                grandTotal = ((parseFloat(TotalAmount+(beamasukAmount>0?beamasukAmount:0))+($('#last_is_vat').val()=='Y'?parseFloat(vat_import):0)).numberFormat(0,'.',','));
                                // lastVAT = ($('#last_is_vat').val()=='Y'?parseFloat(((exch_rate_for_vat*TotalFOBAmount)+beamasukAmount)*{{ $vat }}/100).numberFormat(0,'.',','):0);
                                // grandTotal = ((parseFloat(TotalAmount+(beamasukAmount>0?beamasukAmount:0))+($('#last_is_vat').val()=='Y'?
                                //     parseFloat(((exch_rate_for_vat*TotalFOBAmount)+beamasukAmount)*{{ $vat }}/100):0)).numberFormat(0,'.',','));
                            }else{
                                // lokal
                                $("#lastTotalAmountTmp").val(TotalAmount);
                            }

                            let vHtmlTotal =
                                '<tr id="rowTotal">'+
                                '<td colspan="4" style="text-align: right;"><label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblTotalFOBAmount" id="lblTotalFOBAmount" class="col-form-label">'+(s.supplier_type_id==10?currency_fob:'')+lastTotalFOBAmount+'</label></td>'+
                                '<td>&nbsp;</td>'+
                                '<td style="text-align: right;"><label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">'+lastTotalAmount+'</label></td>'+
                                '<td>&nbsp;</td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlTotal);
                            let vHtmlVAT =
                                '<tr id="rowVAT">'+
                                '<td colspan="4" style="text-align: right;"><label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblVATFOBAmount" id="lblVATFOBAmount" class="col-form-label"></label></td>'+
                                '<td>&nbsp;</td>'+
                                '<td style="text-align: right;"><label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">'+lastVAT+'</label></td>'+
                                '<td>&nbsp;</td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlVAT);
                            let vHtmlGrandTotal =
                                '<tr id="rowGrandTotal">'+
                                '<td colspan="4" style="text-align: right;"><label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblGrandTotalFOBAmount" id="lblGrandTotalFOBAmount" class="col-form-label"></label></td>'+
                                '<td>&nbsp;</td>'+
                                '<td style="text-align: right;"><label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">'+grandTotal+'</label></td>'+
                                '<td>&nbsp;</td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlGrandTotal);

                            if(TotalFOBAmount>0){
                                $("#lastTotalAmountTmp").val(TotalFOBAmount);
                            }else{
                                $("#lastTotalAmountTmp").val(TotalAmount);
                            }

                            calcGrandTotal();
                        }
                    }
                });

            }else{
                alert('The PO or MO number already exists!');
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
