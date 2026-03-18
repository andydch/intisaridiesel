@extends('layouts.app')

@section('style')
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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$queryStock->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">ADJ No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $queryStock->stock_adj_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryStock->branch->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{!! $queryStock->remark !!}</label>
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
                                        <th scope="col" style="width: 20%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Part Type</th>
                                        <th scope="col" style="width: 10%;">Adj</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">SO</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 22%;">Notes</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $lastTotalAmount = 0;
                                        $partHtml = '';
                                    @endphp
                                    @if (old('totalRow'))

                                    @else
                                        @php
                                            $lastIdx = 0;
                                        @endphp
                                        @foreach ($queryStockPart as $qP)
                                            @php
                                                $query = \App\Models\Tx_qty_part::leftJoin('mst_parts AS msp','tx_qty_parts.part_id','=','msp.id')
                                                ->leftJoin('mst_globals AS mg_unit','msp.quantity_type_id','=','mg_unit.id')
                                                ->leftJoin('mst_globals AS mg_part_type','msp.part_type_id','=','mg_part_type.id')
                                                ->select(
                                                    'msp.part_number',
                                                    'msp.part_name',
                                                    'msp.avg_cost',
                                                    'tx_qty_parts.qty AS OH_qty',
                                                    'mg_unit.string_val AS unit_name',
                                                    'mg_part_type.string_val AS part_type_name',
                                                    )
                                                ->addSelect(['SO_qty' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0)')
                                                    ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                                                    ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                                    ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                                    ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                    ->where('tx_sales_order_parts.active','=','Y')
                                                    ->where('txso.active','=','Y')
                                                    ->whereIn('tx_sales_order_parts.id', function (\Illuminate\Database\Query\Builder $query) {
                                                        $query->select('sales_order_part_id')
                                                        ->from('tx_delivery_order_parts')
                                                        ->where('active','=','Y');
                                                    })
                                                ])
                                                ->where([
                                                    'tx_qty_parts.part_id' => $qP->part_id,
                                                    'tx_qty_parts.branch_id' => $queryStock->branch_id,
                                                ])
                                                ->first();

                                                $qty_adj_ = $qP->adjustment;
                                                if($qty_adj_<0){
                                                    $qty_adj_ = $qty_adj_ * -1;
                                                }
                                            @endphp
                                            <tr id="row{{ $lastIdx }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label for="" class="col-form-label">{{ $lastIdx+1 }}.</label>
                                                    <input type="hidden" name="adj_part_id_{{ $lastIdx }}" id="adj_part_id_{{ $lastIdx }}" value="{{ $qP->id }}">
                                                </th>
                                                <td>
                                                    @php
                                                        $partNumber = $qP->part->part_number;
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                    @endphp
                                                    <label for="" class="col-form-label">{{ $partNumber.' : '.$qP->part->part_name }}</label>
                                                </td>
                                                <td><label id="part_type_{{ $lastIdx }}" for="" class="col-form-label">{{ $query->part_type_name }}</label></td>
                                                <td>
                                                    <label id="qty_adj_{{ $lastIdx }}" for="" class="col-form-label">{{ $qP->adjustment }}</label>
                                                </td>
                                                <td><label id="unit_{{ $lastIdx }}" for="" class="col-form-label">{{ $query->unit_name }}</label></td>
                                                <td style="text-align:right;">
                                                    <label id="oh_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($qP->qty_oh,0,'.',',') }}</label>
                                                    <input type="hidden" name="oh_ori_{{ $lastIdx }}" id="oh_ori_{{ $lastIdx }}" value="{{ $qP->qty_oh }}">
                                                </td>
                                                <td style="text-align:right;">
                                                    <label id="so_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($qP->qty_so,0,'.',',') }}</label>
                                                    <input type="hidden" name="so_ori_{{ $lastIdx }}" id="so_ori_{{ $lastIdx }}" value="{{ $qP->qty_so }}">
                                                </td>
                                                <td style="text-align:right;">
                                                    <label id="avg_cost_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($qP->avg_cost,0,'.',',') }}</label>
                                                    <input type="hidden" name="avg_cost_ori_{{ $lastIdx }}" id="avg_cost_ori_{{ $lastIdx }}" value="{{ $qP->avg_cost }}">
                                                </td>
                                                <td style="text-align:right;">
                                                    <label id="total_{{ $lastIdx }}" for="" class="col-form-label">
                                                        {{ number_format(($qty_adj_*$qP->avg_cost),0,'.',',') }}
                                                    </label>
                                                </td>
                                                <td>
                                                    <label id="notes_{{ $lastIdx }}" for="" class="col-form-label">{!! $qP->notes !!}</label>
                                                </td>
                                                @php
                                                    $lastTotalAmount+= ($qty_adj_*$qP->avg_cost);
                                                @endphp
                                            </tr>
                                            @php
                                                $lastIdx += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr id="rowTotal">
                                        <td colspan="8" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
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
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
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
