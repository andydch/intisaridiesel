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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$qInv->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                                <div class="col-xl-9">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">INV No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qInv->invoice_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->customer->customer_unique_code.' - '.$qInv->customer->name }}</label>
                                            </div>
                                            @php
                                                $iRowFK = 0;
                                            @endphp
                                            <input type="hidden" name="totRowFK" id="totRowFK" value="@if(old('totRowFK')){{ old('totRowFK') }}@else{{ $all_selected_FK_count_from_db }}@endif">
                                            <input type="hidden" name="all_selected_FK" id="all_selected_FK" value="@if(old('all_selected_FK')){{ old('all_selected_FK') }}@else{{ $all_selected_FK_from_db }}@endif">
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">FK No</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 97%;text-align:center;">FK No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-fk">
                                                            @php
                                                                $iRow=0;
                                                                $all_selected_FK=explode(",",$all_selected_FK_from_db);
                                                            @endphp
                                                            @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                                                @if ($all_selected_FK[$lastCounter]!='')
                                                                    @php
                                                                        $iRow+=1;
                                                                        $qFK = \App\Models\Tx_delivery_order::where('delivery_order_no','=',$all_selected_FK[$lastCounter])
                                                                        ->first();
                                                                    @endphp
                                                                    <tr id="rowFK{{ $lastCounter }}">
                                                                        <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $iRow }}.</label></td>
                                                                        <td>
                                                                            <label for="" name="fk_no{{ $lastCounter }}" id="fk_no{{ $lastCounter }}"
                                                                                class="col-form-label">{{ $all_selected_FK[$lastCounter] }}</label>
                                                                            <input type="hidden" name="fk_id{{ $lastCounter }}" id="fk_id{{ $lastCounter }}" value="{{ $lastCounter }}">
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Plan Date</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qInv->invoice_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qInv->branch)?$qInv->branch->name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Payment To</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qInv->coa)?$qInv->coa->coa_name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Header</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qInv->header !!}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Footer</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qInv->footer !!}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Paid for</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qInv->remark !!}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created by</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qInv->createdBy->name !!}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col">FK Date</th>
                                        <th scope="col">FK No</th>
                                        <th scope="col">FP No</th>
                                        <th scope="col">SO No</th>
                                        <th scope="col">DPP ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">PPN ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">Cust Doc No</th>
                                    </tr>
                                </thead>
                                <tbody id="new-rowfk-detail">
                                    @php
                                        $grandTotalVal=0;
                                        $totalValbeforeVAT=0;
                                        $totalValafterVAT=0;
                                        $iRow=0;
                                    @endphp
                                    @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                        @if ($all_selected_FK[$lastCounter]!='')
                                            @php
                                                $iRow+=1;
                                                $vat=0;
                                                $qFK = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                                                ->select(
                                                    'tx_delivery_orders.*',
                                                    'tx_delivery_orders.id as faktur_id',
                                                    'tx_tax_invoices.fp_no'
                                                )
                                                ->where('tx_delivery_orders.delivery_order_no','=',$all_selected_FK[$lastCounter])
                                                ->first();
                                            @endphp
                                            @if ($qFK)
                                                @php
                                                    // nota retur - begin
                                                    $retur_total_before_vat = 0;
                                                    $nota_retur = \App\Models\Tx_nota_retur::select(
                                                        'total_before_vat'
                                                    )
                                                    ->whereRaw('approved_by IS NOT null')
                                                    ->where([
                                                        'delivery_order_id'=>$qFK->faktur_id,
                                                        'active'=>'Y',
                                                    ])
                                                    ->first();
                                                    if ($nota_retur){
                                                        $retur_total_before_vat = $nota_retur->total_before_vat;
                                                    }
                                                    // nota retur - end

                                                    $vat=((($qFK->total_before_vat-$retur_total_before_vat)*$qFK->vat_val)/100);
                                                    $all_cust_doc_no_arr=explode(",",$qFK->sales_order_no_all);
                                                    $all_cust_doc_no='';
                                                    $grandTotalVal+=($qFK->total_before_vat-$retur_total_before_vat+$vat);
                                                    // $grandTotalVal+=($qFK->total_after_vat-$retur_total_before_vat);
                                                @endphp
                                                @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                    @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                        @php
                                                            $so = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_cust_doc_no_arr[$c_doc])
                                                            ->first();
                                                        @endphp
                                                        @if ($so)
                                                            @php
                                                                $all_cust_doc_no.=','.$so->customer_doc_no;
                                                            @endphp
                                                        @endif
                                                    @endif
                                                @endfor
                                                <tr id="rowFKdetail{{ $iRow }}">
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ date_format(date_create($qFK->delivery_order_date), 'd/m/Y') }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="fk_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->delivery_order_no }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="fp_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->fp_no }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qFK->sales_order_no_all,1,strlen($qFK->sales_order_no_all)) }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_before_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_before_vat-$retur_total_before_vat),0,'.',',') }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($vat,0,'.',',') }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_before_vat-$retur_total_before_vat+$vat),0,'.',',') }}</label>
                                                        {{-- <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_after_vat-$retur_total_before_vat),0,'.',',') }}</label> --}}
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                    </td>
                                                </tr>
                                                @php
                                                    $totalValbeforeVAT+=($qFK->total_before_vat-$retur_total_before_vat);
                                                    $totalValafterVAT+=($qFK->total_after_vat-$retur_total_before_vat);
                                                @endphp
                                            @endif
                                        @endif
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr id="rowFKdetail-grandtotal">
                                        <td scope="row" colspan="6" style="text-align:right;"><label for="" id="grandtotallbl" class="col-form-label">Grand Total</label></td>
                                        <td scope="row" style="text-align:right;"><label for="" id="grandtotalnumlbl" class="col-form-label">{{ number_format($grandTotalVal,0,'.',',') }}</label></td>
                                        <td scope="row">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="totRowFKdetail" id="totRowFKdetail" value="@if(old('totRowFKdetail')){{ old('totRowFKdetail') }}@else{{ $all_selected_FK_count_from_db }}@endif">
                            <input type="hidden" name="totalValbeforeVAT" id="totalValbeforeVAT" value="@if(old('totalValbeforeVAT')){{ old('totalValbeforeVAT') }}@else{{ $totalValbeforeVAT }}@endif">
                            <input type="hidden" name="totalValafterVAT" id="totalValafterVAT" value="@if(old('totalValafterVAT')){{ old('totalValafterVAT') }}@else{{ $totalValafterVAT }}@endif">
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
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
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
