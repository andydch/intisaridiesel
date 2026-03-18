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
        @include('tx.' . $folder . '.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Receipt Order No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $ro->receipt_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Receipt Order Date</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($ro->receipt_date), 'd/m/Y') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Supplier</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $ro->supplier->name }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Invoice No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $ro->invoice_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Invoice Amount</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ number_format($ro->invoice_amount,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Exchange Rate</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->exchange_rate)?number_format($ro->exchange_rate,0,'.',','):'' }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">B/L No</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $ro->bl_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Vessel No</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $ro->vessel_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Weight Type</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->weight_type_01)?$ro->weight_type_01->title_ind:'' }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Weight Type</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->weight_type_02)?$ro->weight_type_02->title_ind:'' }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">PO/MO No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ substr($ro->po_or_pm_no,1,strlen($ro->po_or_pm_no)) }}</label>
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
                                            <label for="" class="col-sm-3 col-form-label">Currency</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ (!is_null($ro->currency)?$ro->currency->title_ind:'') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Rp Amount</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->exchange_rate)?number_format($ro->exchange_rate*$ro->invoice_amount,0,'.',','):'' }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" style="visibility: hidden;">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Ship To</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ (!is_null($ro->branch)?$ro->branch->name:'') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Ship By</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->courier)?$ro->courier->name:'' }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Gross Weight</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ number_format($ro->gross_weight,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Measurement</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ number_format($ro->measurement,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Remark</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $ro->remark }}</label>
                                        </div>
                                    </div>
                                </div>
                                <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$ro->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="active" class="col-sm-3 col-form-label">Approval Status</label>
                                                <div class="col-sm-9">
                                                    @if(is_null($ro->approved_by) && is_null($ro->canceled_by))
                                                        <label for="active" class="col-sm-9 col-form-label">{{ 'Waiting for Approval' }}</label>
                                                    @endif
                                                    @if(!is_null($ro->approved_by))
                                                        <label for="active" class="col-sm-9 col-form-label">{{ 'Approved by '.$ro->approvedBy->name.' at '.date_format(date_create($ro->approved_at), 'd M Y H:i:s') }}</label>
                                                    @endif
                                                    @if(!is_null($ro->canceled_by))
                                                        <label for="active" class="col-sm-9 col-form-label">{{ 'Rejected by '.$ro->canceledBy->name.' at '.date_format(date_create($ro->canceled_at), 'd M Y H:i:s') }}</label>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="active" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select id="order_appr" name="order_appr">
                                                        <option value="A">Approve</option>
                                                        <option value="R">Reject</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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
                                    {{-- <th scope="col" style="width: 15%;">Part No</th> --}}
                                    <th scope="col" style="width: 60%;">Part Name</th>
                                    <th scope="col" style="width: 7%;">Qty</th>
                                    <th scope="col" style="width: 10%;">Price FOB</th>
                                    <th scope="col" style="width: 10%;">Price</th>
                                    <th scope="col" style="width: 10%;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $lastIdx = 0;
                                    $lastTotalAmount = 0;
                                @endphp
                                @foreach ($ro_part as $rop)
                                    <tr id="row{{ $lastIdx }}">
                                        <th scope="row" style="text-align:right;">
                                            <label for="" class="col-form-label">{{ $lastIdx+1 }}.</label>
                                            <input type="hidden" name="ro_part_id{{ $lastIdx }}" id="ro_part_id{{ $lastIdx }}" value="{{ $rop->id }}">
                                        </th>
                                        <td>
                                            @php
                                                $partNumber = $rop->part->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}" class="col-form-label">{{ $partNumber.' : '.$rop->part->part_name }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="qty{{ $lastIdx }}"" id="qty{{ $lastIdx }}"" class="col-form-label">{{ $rop->qty }}</label>
                                            <input type="hidden" name="qty_on_po{{ $lastIdx }}" id="qty_on_po{{ $lastIdx }}" value="{{ $rop->qty_on_po }}">
                                        </td>
                                        @php
                                            $price_fob = 0;
                                            $price_local = 0;
                                        @endphp
                                        @if ($ro->supplier->supplier_type_id==10)
                                            {{-- international --}}
                                            @php
                                                $exc_rate = $ro->exchange_rate;
                                                $price_fob = $ro->currency->string_val.number_format($rop->final_fob,0,'.',',');
                                                $price_local = number_format($rop->final_fob*$exc_rate,0,'.',',');
                                                $total = number_format($rop->qty*$rop->final_fob*$exc_rate,0,'.',',');

                                                $lastTotalAmount += ($rop->qty*$rop->final_fob*$exc_rate);
                                            @endphp
                                        @endif
                                        @if ($ro->supplier->supplier_type_id==11)
                                            {{-- lokal --}}
                                            @php
                                                $price_fob = 0;
                                                $price_local = number_format($rop->part_price,0,'.',',');
                                                $total = number_format($rop->qty*$rop->part_price,0,'.',',');

                                                $lastTotalAmount += ($rop->qty*$rop->part_price);
                                            @endphp
                                        @endif
                                        <td style="text-align: right;">
                                            <label for="" name="price_fob{{ $lastIdx }}" id="price_fob{{ $lastIdx }}" class="col-form-label">{{ $price_fob }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="price_local{{ $lastIdx }}" id="price_local{{ $lastIdx }}" class="col-form-label">{{ $qCurrency->string_val.$price_local }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}" class="col-form-label">{{ $qCurrency->string_val.$total }}</label>
                                        </td>
                                    </tr>
                                    @php
                                        $lastIdx += 1;
                                    @endphp
                                @endforeach

                                <tr id="rowTotal">
                                    <td colspan="5" style="text-align: right;">
                                        <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                    </td>
                                </tr>
                                
                                <tr id="rowVAT">
                                    <td colspan="5" style="text-align: right;">
                                        <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount*$vat/100,0,'.',',') }}</label>
                                    </td>
                                </tr>
                                <tr id="rowGrandTotal">
                                    <td colspan="5" style="text-align: right;">
                                        <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastTotalAmount*$vat/100),0,'.',',') }}</label>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <input type="button" id="save-approval-status-btn" class="btn btn-light px-5" value="Save">
                                <input type="button" id="back-btn" class="btn btn-light px-5" value="Cancel">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="lastTotalAmountTmp" id="lastTotalAmountTmp" value="@if (old('lastTotalAmountTmp')){{ old('lastTotalAmountTmp') }}@else{{ $lastTotalAmount }}@endif">
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
            // history.back();
            location.href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
