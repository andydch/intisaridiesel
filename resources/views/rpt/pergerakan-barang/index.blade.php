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
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="0">All</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
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
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Period</label>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_start') is-invalid @enderror" maxlength="10"
                                                id="date_start" name="date_start" placeholder="Start Date"
                                                value="@if (old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_end') is-invalid @enderror" maxlength="10"
                                                id="date_end" name="date_end" placeholder="End Date"
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
                        <table class="table table-striped table-bordered" id="parts-movement" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>PARTS NAME</th>
                                    <th>PARTS TYPE</th>
                                    <th>CUSTOMER/SUPPLIER</th>
                                    <th>DATE</th>
                                    <th>DOC NO</th>
                                    <th>PRICE ({{ $qCurrency->string_val }})</th>
                                    <th>BRANCH</th>
                                    <th>QTY IN</th>
                                    <th>QTY OUT</th>
                                    <th>QTY OH</th>
                                </tr>
                            </thead>
                            @isset($reqs)
                            <tbody>
                                @php
                                    $date_start = explode("-",$reqs->date_start)[2].'-'.explode("-",$reqs->date_start)[1].'-'.explode("-",$reqs->date_start)[0];
                                    $date_end = explode("-",$reqs->date_end)[2].'-'.explode("-",$reqs->date_end)[1].'-'.explode("-",$reqs->date_end)[0];

                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->orderBy('name','ASC')
                                    ->get();

                                    $grandtotal = 0;
                                @endphp
                                @foreach ($branches as $branch)
                                    {{-- <tr>
                                        <th>{{ strtoupper($branch->name) }}</th>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr> --}}
                                    @php
                                        $partNumberTmp = '';
                                        $partNameTmp = '';

                                        $queryStockCard = \App\Models\V_stock_card::leftJoin('mst_parts as msp','v_stock_cards.part_id','=','msp.id')
                                        ->leftJoin('mst_globals as part_type','msp.part_type_id','=','part_type.id')
                                        ->select(
                                            'v_stock_cards.tx_date',
                                            'v_stock_cards.customer_or_supplier',
                                            'v_stock_cards.doc_no',
                                            'v_stock_cards.price',
                                            'v_stock_cards.status',
                                            'v_stock_cards.qty',
                                            'v_stock_cards.updated_at as updatedat',
                                            'v_stock_cards.branch_id',
                                            'msp.id as part_id',
                                            'msp.part_number',
                                            'msp.part_name',
                                            'part_type.title_ind as part_type_name',
                                        )
                                        ->where('v_stock_cards.branch_id','=', $branch->id)
                                        ->where('v_stock_cards.tx_date','>=',$date_start)
                                        ->where('v_stock_cards.tx_date','<=',$date_end)
                                        ->where('v_stock_cards.doc_no','NOT LIKE','%Draft%')
                                        ->orderBy('msp.part_number','ASC')
                                        ->orderBy('v_stock_cards.updated_at','ASC')
                                        ->get();
                                    @endphp
                                    @if ($queryStockCard)
                                        @foreach ($queryStockCard as $q)
                                            <tr>
                                                <td>
                                                    @php
                                                        $partNumber = strtoupper($q->part_number);
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                    @endphp
                                                    @if ($partNumberTmp!=$partNumber)
                                                        {{ $partNumber }}
                                                        @php
                                                            $partNumberTmp = $partNumber;
                                                        @endphp
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($partNameTmp!=strtoupper($q->part_name))
                                                        {{ strtoupper($q->part_name) }}
                                                        @php
                                                            $partNameTmp = strtoupper($q->part_name);
                                                        @endphp
                                                    @endif
                                                </td>
                                                <td>{{ $q->part_type_name }}</td>
                                                <td>{{ $q->customer_or_supplier }}</td>
                                                <td>
                                                    @php
                                                        $date=date_create($q->tx_date);
                                                    @endphp
                                                    {{ date_format($date,"d/m/Y") }}
                                                </td>
                                                <td>
                                                    @if (substr($q->doc_no,0,3)=='ROU')
                                                        @php
                                                            $qRO = \App\Models\Tx_receipt_order::select('invoice_no')
                                                            ->where('receipt_no','=',$q->doc_no)
                                                            ->first();
                                                            if($qRO){
                                                                echo $qRO->invoice_no;
                                                            }
                                                        @endphp
                                                    @else
                                                        {{ $q->doc_no }}
                                                    @endif
                                                </td>
                                                <td style="text-align: right;">{{ number_format($q->price,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ strtoupper($branch->initial) }}</td>
                                                <td style="text-align: right;">
                                                    @if ($q->status=='IN')
                                                        {{ $q->qty }}
                                                    @endif
                                                </td>
                                                <td style="text-align: right;">
                                                    @if ($q->status=='OUT')
                                                        {{ $q->qty }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $qQty = \App\Models\V_tx_qty_part::select('qty')
                                                        ->where([
                                                            'part_id' => $q->part_id,
                                                            'branch_id' => $q->branch_id,
                                                        ])
                                                        ->whereRaw('updated_at<\''.$q->updatedat.'\'')
                                                        ->orderBy('updated_at','DESC')
                                                        ->first();
                                                        if ($qQty){
                                                            echo $qQty->qty;
                                                        }else{
                                                            echo 0;
                                                        }
                                                    @endphp
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            @endisset
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
        $("#parts-movement").DataTable({
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
            // $('#time').bootstrapMaterialDatePicker({
            //     date: false,
            //     format: 'HH:mm'
            // });
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
