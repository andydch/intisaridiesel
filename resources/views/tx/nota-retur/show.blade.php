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
                                    <div class="col-xl-8">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">NR No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $qNotaRetur->nota_retur_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Customer</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->customer->name }}</label>
                                            <input type="hidden" name="customer_id" value="{{ $qNotaRetur->customer_id }}">
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">FK No</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->delivery_order->delivery_order_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="all-fk" class="col-sm-3 col-form-label">SO No</label>
                                            <div class="col-sm-9">
                                                <table id="fk-tables" class="table table-bordered mb-0">
                                                    <thead>
                                                        <tr style="width: 100%;">
                                                            <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                            <th scope="col" style="width: 47%;text-align:center;">SO No</th>
                                                            <th scope="col" style="width: 47%;text-align:center;">Cust Doc No</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="new-row-so">
                                                        @php
                                                            $iRow=0;
                                                            $all_selected_SO=explode(",",(old('all_selected_SO')?old('all_selected_SO'):$all_selected_SO));
                                                        @endphp
                                                        @for ($lastCounter=0;$lastCounter<count($all_selected_SO);$lastCounter++)
                                                            @if ($all_selected_SO[$lastCounter]!='')
                                                                @php
                                                                    $iRow+=1;
                                                                    $qSO = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_selected_SO[$lastCounter])
                                                                    ->first();
                                                                @endphp
                                                                <tr id="rowSO{{ $lastCounter }}">
                                                                    <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $iRow }}.</label></td>
                                                                    <td>
                                                                        <label for="" name="so_no_{{ $lastCounter }}" id="so_no_{{ $lastCounter }}"
                                                                            class="col-form-label">{{ $all_selected_SO[$lastCounter] }}</label>
                                                                        <input type="hidden" name="so_id_{{ $lastCounter }}" id="so_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                    </td>
                                                                    <td>
                                                                        <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                            class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                    </td>
                                                                    {{-- <td style="text-align: center;"><input type="checkbox" id="rowSOCheck{{ $lastCounter }}" value="{{ $lastCounter }}"></td> --}}
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Remark</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->remark }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Created By</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->createdBy->name }}</label>
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
                                    <th scope="col" style="width: 15%;">Part No</th>
                                    <th scope="col" style="width: 15%;">Part Name</th>
                                    <th scope="col" style="width: 5%;">Qty</th>
                                    <th scope="col" style="width: 5%;">Qty Retur</th>
                                    <th scope="col" style="width: 5%;">Unit</th>
                                    <th scope="col" style="width: 11%;">Price ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Total ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 5%;">SO No</th>
                                </tr>
                            </thead>
                            <tbody id="new-row-so-part">
                                @php
                                    $lastIdx = 0;
                                    $totalPrice = 0;
                                    $lastSoPartCounter=0;
                                @endphp
                                @foreach ($qNotaReturPart as $qNRpart)
                                    <tr id="rowSOdetail{{ $lastSoPartCounter }}">
                                        @php
                                            $parts = \App\Models\Tx_sales_order_part::leftJoin('mst_parts AS mp','tx_sales_order_parts.part_id','=','mp.id')
                                            ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
                                            ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
                                            ->leftJoin('tx_sales_orders AS tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                                            ->select(
                                                'tx_sales_order_parts.id AS sales_order_part_id',
                                                'tx_sales_order_parts.order_id AS sales_order_id',
                                                'tx_sales_order_parts.part_id',
                                                'tx_sales_order_parts.part_no',
                                                'tx_sales_order_parts.qty',
                                                'tx_sales_order_parts.price',
                                                'mp.part_name',
                                                'mg01.string_val AS part_unit',
                                                'mp.weight',
                                                'mg02.string_val AS weight_unit',
                                                'tx_so.sales_order_no',
                                            )
                                            ->where([
                                                'tx_sales_order_parts.id' => $qNRpart->sales_order_part_id,
                                                'tx_sales_order_parts.active' => 'Y'
                                            ])
                                            ->first();
                                        @endphp
                                        @if ($parts)
                                            @php
                                                $totalPrice += ($parts->price*$qNRpart->qty_retur);

                                                $partNumber = $parts->part_no;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <td scope="row" style="text-align:right;">
                                                <label for="" id="row_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $lastSoPartCounter+1 }}.</label>
                                                <input type="hidden" id="so_id_linktopart_{{ $lastSoPartCounter }}" name="so_id_linktopart_{{ $lastSoPartCounter }}"
                                                    value="{{ $parts->sales_order_id }}">
                                                <input type="hidden" id="sales_order_part_id{{ $lastSoPartCounter }}" name="sales_order_part_id{{ $lastSoPartCounter }}"
                                                    value="{{ $qNRpart->sales_order_part_id }}">
                                            </td>
                                            <td scope="row" style="text-align:left;">
                                                <label for="" id="part_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                <input type="hidden" id="part_id_{{ $lastSoPartCounter }}" name="part_id_{{ $lastSoPartCounter }}"
                                                    value="{{ $parts->part_id }}">
                                            </td>
                                            <td scope="row" style="text-align:left;">
                                                <label for="" id="part_name_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                            </td>
                                            <td scope="row" style="text-align:right;">
                                                <label for="" id="qty_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                <input type="hidden" id="qty_do_{{ $lastSoPartCounter }}" name="qty_do_{{ $lastSoPartCounter }}" value="{{ $parts->qty }}">
                                            </td>
                                            <td scope="row" style="text-align:right;">
                                                <label for="" id="qty_retur{{ $lastSoPartCounter }}" class="col-form-label">{{ $qNRpart->qty_retur }}</label>
                                            </td>
                                            <td scope="row" style="text-align:left;">
                                                <label for="" id="unit_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_unit }}</label>
                                            </td>
                                            <td scope="row" style="text-align:right;">
                                                <label for="" id="price_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price,0,'.',',') }}</label>
                                                <input type="hidden" id="price_ori_{{ $lastSoPartCounter }}" name="price_ori_{{ $lastSoPartCounter }}"
                                                    value="{{ $parts->price }}">
                                            </td>
                                            <td scope="row" style="text-align:right;">
                                                <label for="" id="total_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price*$qNRpart->qty_retur,0,'.',',') }}</label>
                                                <input type="hidden" id="total_ori_{{ $lastSoPartCounter }}" name="total_ori_{{ $lastSoPartCounter }}" value="{{ $qNRpart->final_price*$qNRpart->qty_retur }}">
                                            </td>
                                            <td scope="row" style="text-align:left;">
                                                <label for="" id="so_no_linktopart_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->sales_order_no }}</label>
                                            </td>
                                        @endif
                                    </tr>
                                    @php
                                        $lastSoPartCounter++;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="text-align: right;">Total before VAT</td>
                                    <td style="text-align: right;">
                                        <label for="" id="total_before_vat">{{ $qCurrency->string_val.number_format($totalPrice,0,'.',',') }}</label>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">VAT</td>
                                    <td style="text-align: right;">
                                        <label for="" id="vat_total">{{ $qCurrency->string_val.number_format($totalPrice*$vat/100,0,'.',',') }}</label>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right;">Grand Total</td>
                                    <td style="text-align: right;">
                                        <label for="" id="grand_total">{{ $qCurrency->string_val.number_format($totalPrice+($totalPrice*$vat/100),0,'.',',') }}</label>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr>
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
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
