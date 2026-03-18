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
            <form id="submit-form" action="" method="POST" enctype="application/x-www-form-urlencoded">
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
                                                <label for="" class="col-sm-3 col-form-label">Purchase Retur No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qRo->purchase_retur_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Supplier</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($qRo->supplier)?$qRo->supplier->name:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Invoice No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ !is_null($qRo->receipt_order)?$qRo->receipt_order->invoice_no:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Ship By</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    @switch($qRo->courier_type)
                                                        @case(env('AMBIL_SENDIRI'))
                                                            {{ env('AMBIL_SENDIRI_STR') }}
                                                            @break

                                                        @case(env('DIANTAR'))
                                                            {{ env('DIANTAR_STR') }}
                                                            @break

                                                        @case(env('COURIER'))
                                                            {{ env('COURIER_STR').(!is_null($qRo->courier)?' - '.$qRo->courier->name:'') }}
                                                            @break

                                                        @default
                                                            {{ '' }}
                                                    @endswitch
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qRo->remark }}</label>
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
                                                <label for="" class="col-sm-3 col-form-label">Receipt Order No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ !is_null($qRo->receipt_order)?$qRo->receipt_order->receipt_no:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Journal Type</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($qRo->receipt_order)?$qRo->receipt_order->journal_type_id:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qRo->branch)?$qRo->branch->name:'') }}</label>
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
                                        <th scope="col" style="width: 20%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Qty Retur</th>
                                        <th scope="col" style="width: 10%;">Price</th>
                                        <th scope="col" style="width: 10%;">Total Retur</th>
                                        <th scope="col" style="width: 25%;">Description</th>
                                        {{-- <th scope="col" style="width: 3%;">Del</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $totalRetur = 0;
                                        $lastIdx = 0;
                                    @endphp
                                    @foreach ($qRoPart as $rowPart)
                                        <tr id="row{{ $lastIdx }}">
                                            @php
                                                $query = \App\Models\Tx_purchase_retur_part::leftJoin('mst_parts','tx_purchase_retur_parts.part_id','=','mst_parts.id')
                                                ->select(
                                                    'tx_purchase_retur_parts.part_id',
                                                    'tx_purchase_retur_parts.final_cost',
                                                    'mst_parts.part_number',
                                                    'mst_parts.part_name'
                                                )
                                                ->where([
                                                    'tx_purchase_retur_parts.purchase_retur_id' => $qRo->id,
                                                    'tx_purchase_retur_parts.part_id' => $rowPart->part_id,
                                                    'tx_purchase_retur_parts.active' => 'Y'
                                                ])
                                                ->first();
                                            @endphp
                                            <th scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label">{{ $lastIdx+1 }}.</label>
                                                <input type="hidden" name="row_part_id_{{ $lastIdx }}" id="row_part_id_{{ $lastIdx }}" value="{{ $rowPart->id }}">
                                                <input type="hidden" name="part_id{{ $lastIdx }}" id="part_id{{ $lastIdx }}" value="{{ $rowPart->part_id }}">
                                            </th>
                                            <td>
                                                @php
                                                    $partNumber = $query->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                <label for="" name="part_no{{ $lastIdx }}" id="part_no{{ $lastIdx }}" class="col-form-label">{{ $partNumber }}</label>
                                            </td>
                                            <td><label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}" class="col-form-label">{{ !is_null($query)?$query->part_name:'' }}</label></td>
                                            <td style="text-align: right;">
                                                <label for="" name="qty{{ $lastIdx }}" id="qty{{ $lastIdx }}" class="col-form-label">{{ $rowPart->qty }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="qty_retur{{ $lastIdx }}" id="qty_retur{{ $lastIdx }}" class="col-form-label">{{ $rowPart->qty_retur }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="price{{ $lastIdx }}" id="price{{ $lastIdx }}" class="col-form-label">{{ $qCurrency->string_val.number_format($rowPart->final_cost,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total_retur{{ $lastIdx }}" id="total_retur{{ $lastIdx }}" class="col-form-label">{{ $qCurrency->string_val.number_format(($rowPart->qty_retur*$rowPart->final_cost),0,'.',',') }}</label>
                                                @php
                                                    $totalRetur += ($rowPart->qty_retur*$rowPart->final_cost);
                                                @endphp
                                            </td>
                                            <td>
                                                <label for="" name="desc_part{{ $lastIdx }}" id="desc_part{{ $lastIdx }}" class="col-form-label">{{ $rowPart->description }}</label>
                                            </td>
                                            {{-- <td style="text-align:center;"><input type="checkbox" id="rowCheck{{ $lastIdx }}" value="{{ $lastIdx }}"></td> --}}
                                        </tr>
                                        @php
                                            $lastIdx += 1;
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="total_lbl" id="total_lbl" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="total_lbl_amount" id="total_lbl_amount" class="col-form-label">{{ $qCurrency->string_val.number_format($totalRetur,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="vat_lbl" id="vat_lbl" class="col-form-label">VAT {{ $qRo->vat_val>0?number_format($qRo->vat_val,0,'.',',').'%':'' }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="vat_lbl_amount" id="vat_lbl_amount" class="col-form-label">{{ $qCurrency->string_val.number_format($totalRetur*$qRo->vat_val/100,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="grandtotal_lbl" id="grandtotal_lbl" class="col-form-label">Grand Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="grandtotal_lbl_amount" id="grandtotal_lbl_amount" class="col-form-label">{{ $qCurrency->string_val.number_format(($totalRetur*$qRo->vat_val/100)+$totalRetur,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
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
