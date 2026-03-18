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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$stockDisAsm->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="border p-4 rounded">
                                    <div class="col-xl-6">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">SD No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $stockDisAsm->stock_disassembly_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Date</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($stockDisAsm->stock_disassembly_date), 'd M Y') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Branch</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ ($stockDisAsm->branch_id!=null)?$stockDisAsm->branch->name:$userLogin->branch->name }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Part No</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $stockDisAsm->part->part_number }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Part Name</label>
                                            <label for="" id="part_name" class="col-sm-9 col-form-label">{{ $stockDisAsm->part->part_name }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">AVG Cost</label>
                                            <label for="" id="avg-cost-part-to-be-disassembly" class="col-sm-9 col-form-label">
                                                {{ $qCurrency->string_val.number_format($stockDisAsm->avg_cost*$stockDisAsm->qty,0,'.',',') }}
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Qty</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $stockDisAsm->qty }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Remark</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ $stockDisAsm->remark }}</label>
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
                            <input type="hidden" id="totalRow" name="totalRow" class="@error('totalRow') is-invalid @enderror" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 2%;text-align:center;">#</th>
                                        <th scope="col" style="width: 15%;">To Be Part No</th>
                                        <th scope="col" style="width: 15%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Cost</th>
                                        <th scope="col" style="width: 10%;">Total Cost</th>
                                        <th scope="col" style="width: 10%;">AVG Cost</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $total_cost = 0;
                                    @endphp
                                    @if (old('totalRow'))
                                        {{-- empty --}}
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($stockDisAsmPart as $sd)
                                            <tr id="row{{ $i }}">
                                                @php
                                                    $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                                                    ->select(
                                                        'mst_parts.*',
                                                        'txQty.qty'
                                                    )
                                                    ->where([
                                                        'mst_parts.id' => $sd->part_id,
                                                        'txQty.branch_id' => $branch_id
                                                    ])
                                                    ->first();
                                                @endphp
                                                @if($partNo)
                                                    @php
                                                        $total_cost += $sd->cost*$sd->qty;
                                                        $avg_cost = (($partNo->avg_cost*$partNo->qty)+($sd->cost*$sd->qty))/($partNo->qty+$sd->qty);
                                                    @endphp
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="row-no{{ $i }}" class="col-form-label">{{ $i + 1 }}.</label>
                                                        <input type="hidden" name="sd_part_id{{ $i }}" id="sd_part_id{{ $i }}" value="{{ $sd->id }}">
                                                    </th>
                                                    <td>
                                                        @php
                                                            $partNumber = $sd->part->part_number;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <label for="" id="" class="col-form-label">{{ $partNumber }}</label>
                                                    </td>
                                                    <td>
                                                        <label for="" id="" class="col-form-label">{{ $partNo->part_name }}</label>
                                                    </td>
                                                    <td>
                                                        <label for="" id="" class="col-form-label">{{ $sd->qty }}</label>
                                                    </td>
                                                    <td>
                                                        <label for="" id="" class="col-form-label">{{ $partNo->quantity_type->string_val }}</label>
                                                    </td>
                                                    <td>
                                                        <label for="" id="" class="col-form-label">{{ $qCurrency->string_val.number_format($sd->cost,0,'.',',') }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="total-cost-{{ $i }}" class="col-form-label">{{ $qCurrency->string_val.number_format($sd->cost*$sd->qty,0,'.',',') }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="avg-cost-{{ $i }}" class="col-form-label">{{ $qCurrency->string_val.number_format($avg_cost,0,'.',',') }}</label>
                                                    </td>
                                                @endif
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" id="total-avg-cost-lbl" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" id="total-cost-val" class="col-form-label">{{ $qCurrency->string_val.number_format($total_cost,0,'.',',') }}</label>
                                        </td>
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
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
