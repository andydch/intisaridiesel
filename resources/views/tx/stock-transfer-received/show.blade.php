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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$qStock->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-12 border p-4 rounded">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">SM No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qStock->stock_transfer_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">From</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qStock->branch_from->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">To</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qStock->branch_to->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qStock->remark }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Approval Status</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    @if(!is_null($qStock->approved_by) && $qStock->active=='Y')
                                                        {{ 'Approved at '.
                                                            date_format(date_add(date_create($qStock->approved_at), date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), "d-M-Y H:i:s").
                                                            ' by '.$qStock->approvedBy->name }}
                                                    @endif
                                                    @if(!is_null($qStock->canceled_by) && $qStock->active=='Y')
                                                        {{ 'Rejected at '.
                                                            date_format(date_add(date_create($qStock->rejected_at), date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), "d-M-Y H:i:s").
                                                            ' by '.$qStock->canceledBy->name }}
                                                    @endif
                                                    @if(is_null($qStock->approved_by) && $qStock->active=='Y' && strpos($qStock->stock_transfer_no,'Draft')==0)
                                                        {{ 'Waiting for Approval' }}
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created Date</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ date_format(date_add(date_create($qStock->created_at), date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), "d-M-Y H:i:s") }}
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created By</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qStock->createdBy->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Received Date</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ (!is_null($qStock->received_at)?
                                                        date_format(date_add(date_create($qStock->received_at), date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), "d-M-Y H:i:s"):
                                                        '') }}
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Received By</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qStock->receivedBy)?$qStock->receivedBy->name:'') }}</label>
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
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 2%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part Number</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Part Type</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Unit</th>
                                        <th scope="col" style="width: 18%;">AVG Cost</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if (old('totalRow'))

                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($qStockPart as $qSPart)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label for="" id="" class="col-form-label">{{ $i + 1 }}.</label>
                                                    <input type="hidden" name="part_row_id{{ $i }}" id="part_row_id{{ $i }}" value="{{ $qSPart->id }}">
                                                </th>
                                                @php
                                                    $partNo = \App\Models\Mst_part::where([
                                                        'id' => $qSPart->part_id,
                                                    ])
                                                        ->first();
                                                @endphp
                                                <td>
                                                    @php
                                                        $partNumber = $qSPart->part->part_number;
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                    @endphp
                                                    <label for="" id="" class="col-form-label">{{ $partNumber }}</label>
                                                </td>
                                                <td>
                                                    <label for="" id="part-name-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->part_name:'') }}</label>
                                                </td>
                                                <td>
                                                    <label for="" id="part-type-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->part_type->title_ind:'') }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" id="" class="col-form-label">{{ $qSPart->qty }}</label>
                                                </td>
                                                <td>
                                                    <label for="" id="part-unit-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->quantity_type->string_val:'') }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" id="avg-cost-{{ $i }}" class="col-form-label">
                                                        @if ($qSPart->last_avg_cost!=null)
                                                            {{ number_format($qSPart->last_avg_cost,0,"",".") }}
                                                        @else
                                                            @php
                                                                $qPart = \App\Models\V_log_avg_cost::where('part_id', '=', $qSPart->part_id)
                                                                ->whereRaw('updated_at<(SELECT created_at 
                                                                    FROM tx_general_journals 
                                                                    WHERE module_no=\''.$qStock->stock_transfer_no.'\')')
                                                                // ->whereRaw('updated_at<\''.$qSPart->updated_at.'\'')
                                                                ->orderBy('updated_at', 'DESC')
                                                                ->orderBy('row_id', 'ASC')
                                                                ->limit(1)
                                                                ->first();
                                                            @endphp
                                                            @if ($qPart)
                                                                {{ number_format($qPart->avg_cost,0,"",".") }}
                                                            @endif
                                                        @endif
                                                    </label>
                                                </td>
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
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
                                    @if (is_null($qStock->received_by))
                                        <input type="button" id="save-approval-status-btn" class="btn btn-primary px-5" value="Received">
                                    @endif
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
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
        $("#save-approval-status-btn").click(function() {
            if(!confirm("After this, the parts will be sent to the destination warehouse.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            history.back();
        });
    });
</script>
@endsection
