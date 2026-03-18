@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include(ENV('REPORT_FOLDER_NAME').'.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Period</label>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_start') is-invalid @enderror" maxlength="10"
                                                id="date_start" name="date_start" placeholder="Date Start"
                                                value="@if (old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_end') is-invalid @enderror" maxlength="10"
                                                id="date_end" name="date_end" placeholder="Date End"
                                                value="@if (old('date_end')){{ old('date_end') }}@else{{ (isset($reqs)?$reqs->date_end:'') }}@endif">
                                            @error('date_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            {{-- <input type="button" id="generate-report" class="btn btn-primary px-5" value="Generate"> --}}
                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="display: none;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="change-in-avg-cost" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>PARTS NAME</th>
                                    <th>PARTS TYPE</th>
                                    <th>DOC NO</th>
                                    <th>SUPPLIER</th>
                                    <th>DATE</th>
                                    <th>QTY OH</th>
                                    <th>QTY IN</th>
                                    <th>AVG AWAL</th>
                                    <th>COST BELI</th>
                                    <th>AVG AKHIR</th>
                                    <th>BRANCH</th>
                                </tr>
                            </thead>
                            <tbody>
                            @isset($reqs)
                                @php
                                    $date_start = explode("-",$reqs->date_start)[2].'-'.explode("-",$reqs->date_start)[1].'-'.explode("-",$reqs->date_start)[0];
                                    $date_end = explode("-",$reqs->date_end)[2].'-'.explode("-",$reqs->date_end)[1].'-'.explode("-",$reqs->date_end)[0];
                                    $part_number_tmp = '';
                                    $part_name_tmp = '';

                                    $queryParts = \App\Models\V_avg_cost_change::where('doc_date','>=',$date_start)
                                    ->where('doc_date','<=',$date_end)
                                    ->orderBy('part_number','ASC')
                                    ->orderBy('doc_date','ASC')
                                    ->orderBy('updated_at','ASC')
                                    ->get();
                                @endphp
                                @foreach ($queryParts as $part)
                                    <tr>
                                        <td>
                                            @php
                                                $partNumber = strtoupper($part->part_number);
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            @if ($part_number_tmp!=$partNumber)
                                                {{ strtoupper($partNumber) }}
                                                @php
                                                    $part_number_tmp = $partNumber;
                                                @endphp
                                            @endif
                                        </td>
                                        <td>
                                            @if ($part_name_tmp!=$part->part_name)
                                                {{ strtoupper($part->part_name) }}
                                                @php
                                                    $part_name_tmp = $part->part_name;
                                                @endphp
                                            @endif
                                        </td>
                                        <td>{{ $part->part_type_name }}</td>
                                        <td>{{ $part->doc_no }}</td>
                                        <td>{{ $part->supplier_or_customer }}</td>
                                        <td>
                                            @php
                                                $date=date_create($part->doc_date);
                                            @endphp
                                            {{ date_format($date,"d/m/Y") }}
                                        </td>
                                        <td>
                                            @php
                                                $totOH = 0;
                                                $branches = \App\Models\Mst_branch::where('active','=','Y')
                                                ->get();
                                            @endphp
                                            @foreach ($branches as $branch)
                                                @php
                                                    $qQty = \App\Models\V_tx_qty_part::where([
                                                        'part_id' => $part->part_id,
                                                        'branch_id' => $branch->id,
                                                    ])
                                                    ->where('updated_at','<',$part->updated_at)
                                                    ->orderBy('updated_at','DESC')
                                                    ->first();
                                                    if ($qQty){
                                                        $totOH += $qQty->qty;
                                                    }
                                                @endphp
                                            @endforeach
                                            {{ $totOH }}
                                        </td>
                                        <td>{{ $part->qty }}</td>
                                        @php
                                            // $avgBefore = $part->avg_cost_before;
                                            $avgBefore = 0;
                                            $qAvgCostBefore = \App\Models\V_avg_cost_change::whereRaw('updated_at<\''.$part->updated_at.'\'')
                                            ->where('part_id','=',$part->part_id)
                                            ->orderBy('part_number','ASC')
                                            // ->orderBy('doc_date','ASC')
                                            ->orderBy('updated_at','DESC')
                                            ->first();
                                            if ($qAvgCostBefore){
                                                $avgBefore = $qAvgCostBefore->avg_cost_after;
                                            }
                                        @endphp
                                        <td style="text-align: right;">{{ number_format($avgBefore,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($part->price,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($part->avg_cost_after,0,'.',',') }}</td>
                                        <td>{{ $part->branch_initial }}</td>
                                    </tr>
                                @endforeach
                            @endisset
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>PARTS NAME</th>
                                    <th>PARTS TYPE</th>
                                    <th>DOC NO</th>
                                    <th>SUPPLIER</th>
                                    <th>DATE</th>
                                    <th>QTY OH</th>
                                    <th>QTY IN</th>
                                    <th>AVG AWAL</th>
                                    <th>COST BELI</th>
                                    <th>AVG AKHIR</th>
                                    <th>BRANCH</th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#change-in-avg-cost").DataTable({
            'ordering': false,
        });

        $("#generate-report").click(function() {
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function() {
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            // $('#date-time').bootstrapMaterialDatePicker({
            //     format: 'YYYY-MM-DD HH:mm'
            // });
            $('#date_start').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            $('#date_end').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
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
