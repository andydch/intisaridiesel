@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($qKwi->kwitansi_no)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">KW No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qKwi->kwitansi_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($qKwi->customer)?$qKwi->customer->name:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Date</label>
                                                <label for="" class="col-sm-3 col-form-label">{{ date_format(date_create($qKwi->invoice_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Expired Date</label>
                                                <label for="" class="col-sm-3 col-form-label">{{ date_format(date_create($qKwi->invoice_expired_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Header</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qKwi->header !!}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Footer</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $qKwi->footer !!}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qKwi->remark }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Approval Status</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    @if(!is_null($qKwi->approved_by) && $qKwi->active=='Y')
                                                        {{ 'Approved at '.date_format(date_create($qKwi->approved_at), 'd-M-Y H:i:s').' by '.$qKwi->approvedBy->name }}
                                                    @endif
                                                    @if(!is_null($qKwi->canceled_by) && $qKwi->active=='Y')
                                                        {{ 'Rejected at '.date_format(date_create($qKwi->canceled_at), 'd-M-Y H:i:s').' by '.$qKwi->canceledBy->name }}
                                                    @endif
                                                    @if(is_null($qKwi->approved_by) && is_null($qKwi->canceled_at) && $qKwi->active=='Y' && strpos($qKwi->kwitansi_no,'Draft')==0)
                                                        {{ 'Waiting for Approval' }}
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-3">
                                                    <select class="form-select single-select @error('order_appr') is-invalid @enderror" id="order_appr" name="order_appr">
                                                        <option value="A">Approve</option>
                                                        <option value="R">Reject</option>
                                                    </select>
                                                    @error('order_appr')
                                                        <div class="invalid-feedback">{!! $message !!}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                {{-- <label for="" class="col-sm-3 col-form-label">SO No</label> --}}
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">NP No</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="new-row-so">
                                                                    @php
                                                                        $all_selected_NP_from_db = explode(",",$all_selected_NP_from_db);
                                                                        $i = 1;
                                                                    @endphp
                                                                    @foreach ($all_selected_NP_from_db as $do_perId)
                                                                        @if ($do_perId!='')
                                                                            <tr id="row{{ $i }}">
                                                                                <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i }}.</label></th>
                                                                                <td>
                                                                                    <label for="" name="so_no{{ $i }}" id="so_no{{ $i }}" class="col-form-label">{{ $do_perId }}</label>
                                                                                </td>
                                                                            </tr>
                                                                            @php
                                                                                $i += 1;
                                                                            @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
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
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col">Date</th>
                                        <th scope="col">NP No</th>
                                        <th scope="col">SJ No</th>
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
                                    @for ($lastCounter=0;$lastCounter<count($all_selected_NP_from_db);$lastCounter++)
                                        @if ($all_selected_NP_from_db[$lastCounter]!='')
                                            @php
                                                $iRow+=1;
                                                $qNP = \App\Models\Tx_delivery_order_non_tax::where('tx_delivery_order_non_taxes.delivery_order_no','=',$all_selected_NP_from_db[$lastCounter])
                                                ->first();
                                            @endphp
                                            @if ($qNP)
                                                @php
                                                    $all_cust_doc_no_arr=explode(",",$qNP->sales_order_no_all);
                                                    $all_cust_doc_no='';
                                                    $grandTotalVal+=$qNP->total_after_vat;
                                                @endphp
                                                @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                    @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                        @php
                                                            $so = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_cust_doc_no_arr[$c_doc])
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
                                                        <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_date }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="fk_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_no }}</label>
                                                    </td>
                                                    {{-- <td scope="row" style="text-align:left;">
                                                        <label for="" id="fp_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->fp_no }}</label>
                                                    </td> --}}
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qNP->sales_order_no_all,1,strlen($qNP->sales_order_no_all)) }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_price_dtl{{ $iRow }}" class="col-form-label">{{ number_format($qNP->total_price,0,'.',',') }}</label>
                                                    </td>
                                                    {{-- <td scope="row" style="text-align:right;">
                                                        <label for="" id="vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($vat,0,'.',',') }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($qNP->total_after_vat,0,'.',',') }}</label>
                                                    </td> --}}
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                    </td>
                                                </tr>
                                                @php
                                                    $totalValbeforeVAT+=$qNP->total_price;
                                                    // $totalValafterVAT+=$qNP->total_after_vat;
                                                @endphp
                                            @endif
                                        @endif
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr id="rowFKdetail-grandtotal">
                                        <td scope="row" colspan="3" style="text-align:right;"><label for="" id="grandtotallbl" class="col-form-label">Grand Total</label></td>
                                        <td scope="row" style="text-align:right;"><label for="" id="grandtotalnumlbl" class="col-form-label">{{ number_format($totalValbeforeVAT,0,'.',',') }}</label></td>
                                        <td scope="row">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="totRowFKdetail" id="totRowFKdetail" value="@if(old('totRowFKdetail')){{ old('totRowFKdetail') }}@else{{ $all_selected_NP_count_from_db }}@endif">
                            <input type="hidden" name="totalValbeforeVAT" id="totalValbeforeVAT" value="@if(old('totalValbeforeVAT')){{ old('totalValbeforeVAT') }}@else{{ $totalValbeforeVAT }}@endif">
                            {{-- <input type="hidden" name="totalValafterVAT" id="totalValafterVAT" value="@if(old('totalValafterVAT')){{ old('totalValafterVAT') }}@else{{ $totalValafterVAT }}@endif"> --}}
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save-approval-status-btn" class="btn btn-light px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-light px-5" value="Back">
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
    $(document).ready(function() {
        $("#save-approval-status-btn").click(function() {
            if(!confirm("The approval status will be changed, after this it cannot be undone!\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
