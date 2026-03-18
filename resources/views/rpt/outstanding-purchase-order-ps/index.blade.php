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
                                    {{-- <div class="row mb-3">
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="0">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
                                                @endphp
                                                @foreach ($branches as $branch)
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> --}}
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
                                    {{-- <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if (old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
                                            @error('lokal_input')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
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
                        @isset($reqs)
                        <table class="table table-striped table-bordered" id="purchase-summary-per-branch-per-brand" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">SUPPLIER NAME</th>
                                    <th style="text-align: center;">PO NO</th>
                                    <th style="text-align: center;">DATE</th>
                                    <th style="text-align: center;">PARTS NO</th>
                                    <th style="text-align: center;">PARTS NAME</th>
                                    <th style="text-align: center;">ORD QTY</th>
                                    <th style="text-align: center;">HARGA DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL DPP({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">ESTIMASI DATE</th>
                                    <th style="text-align: center;">PIC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $supplier_name='';

                                    $purchase_orders = \App\Models\Tx_purchase_order::leftJoin('mst_suppliers as ms_sp','tx_purchase_orders.supplier_id','=','ms_sp.id')
                                    ->leftJoin('userdetails','tx_purchase_orders.created_by','=','userdetails.user_id')
                                    ->select(
                                        'tx_purchase_orders.id as po_id',
                                        'tx_purchase_orders.purchase_no',
                                        'tx_purchase_orders.purchase_date',
                                        'tx_purchase_orders.est_supply_date',
                                        'ms_sp.name as supplier_name',
                                        'userdetails.initial as user_initial',
                                    )
                                    ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
                                    // ->whereNotIn('tx_purchase_orders.id',function($query){
                                    //     $query->select('tx_rop.po_mo_id')
                                    //     ->from('tx_receipt_order_parts as tx_rop')
                                    //     ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                    //     ->where('tx_rop.po_mo_no','LIKE','PO%')
                                    //     ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                    //     ->where([
                                    //         'tx_rop.is_partial_received'=>'N',
                                    //         'tx_rop.active'=>'Y',
                                    //         'tx_ro.active'=>'Y',
                                    //     ]);
                                    // })
                                    ->whereNotIn('tx_purchase_orders.purchase_no',function($query){
                                        $query->select('tx_rop.po_mo_no')
                                        ->from('tx_receipt_order_parts as tx_rop')
                                        ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                        ->where([
                                            'tx_rop.is_partial_received'=>'N',
                                            'tx_rop.active'=>'Y',
                                            'tx_ro.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_purchase_orders.purchase_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_purchase_orders.purchase_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_purchase_orders.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_purchase_orders.active'=>'Y',
                                    ])
                                    ->orderBy('tx_purchase_orders.purchase_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($purchase_orders as $po)
                                    @php
                                        $totDppPerPO=0;
                                        $purchase_no='';
                                        $purchase_order_parts = \App\Models\Tx_purchase_order_part::leftJoin('mst_parts as msp','tx_purchase_order_parts.part_id','=','msp.id')
                                        ->select(
                                            'tx_purchase_order_parts.qty',
                                            'tx_purchase_order_parts.price',
                                            'msp.part_number',
                                            'msp.part_name',
                                        )
                                        ->where([
                                            'tx_purchase_order_parts.order_id'=>$po->po_id,
                                            'tx_purchase_order_parts.active'=>'Y',
                                        ])
                                        ->orderBy('msp.part_number','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($purchase_order_parts as $pop)
                                        <tr>
                                            <td>{{ ($supplier_name!=$po->supplier_name)?$po->supplier_name:'' }}</td>
                                            <td>{{ ($purchase_no!=$po->purchase_no)?$po->purchase_no:'' }}</td>
                                            <td style="text-align: center;">{{ $po->purchase_date }}</td>
                                            <td>{{ $pop->part_number }}</td>
                                            <td>{{ $pop->part_name }}</td>
                                            <td style="text-align: right;">{{ $pop->qty }}</td>
                                            <td style="text-align: right;">{{ number_format($pop->price,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format(($pop->price*$pop->qty),0,'.',',') }}</td>
                                            <td style="text-align: center;">{{ $po->est_supply_date }}</td>
                                            <td style="text-align: center;">{{ $po->user_initial }}</td>
                                        </tr>
                                        @php
                                            $totDppPerPO+=($pop->price*$pop->qty);
                                            $supplier_name=$po->supplier_name;
                                            $purchase_no=$po->purchase_no;
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;font-weight:700;">{{ number_format($totDppPerPO,0,'.',',') }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
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
                                    </tr>
                                @endforeach

                                @php
                                    $purchase_memos = \App\Models\Tx_purchase_memo::leftJoin('mst_suppliers as ms_sp','tx_purchase_memos.supplier_id','=','ms_sp.id')
                                    ->leftJoin('userdetails','tx_purchase_memos.created_by','=','userdetails.user_id')
                                    ->select(
                                        'tx_purchase_memos.id as mo_id',
                                        'tx_purchase_memos.memo_no',
                                        'tx_purchase_memos.memo_date',
                                        'ms_sp.name as supplier_name',
                                        'userdetails.initial as user_initial',
                                    )
                                    ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%')
                                    // ->whereNotIn('tx_purchase_memos.id',function($query){
                                    //     $query->select('tx_rop.po_mo_id')
                                    //     ->from('tx_receipt_order_parts as tx_rop')
                                    //     ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                    //     ->where('tx_rop.po_mo_no','LIKE','MO%')
                                    //     ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                    //     ->where([
                                    //         'tx_rop.is_partial_received'=>'N',
                                    //         'tx_rop.active'=>'Y',
                                    //         'tx_ro.active'=>'Y',
                                    //     ]);
                                    // })
                                    ->whereNotIn('tx_purchase_memos.memo_no',function($query){
                                        $query->select('tx_rop.po_mo_no')
                                        ->from('tx_receipt_order_parts as tx_rop')
                                        ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                        ->where([
                                            'tx_rop.is_partial_received'=>'N',
                                            'tx_rop.active'=>'Y',
                                            'tx_ro.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_purchase_memos.memo_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_purchase_memos.memo_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'tx_purchase_memos.active'=>'Y',
                                    ])
                                    ->orderBy('tx_purchase_memos.memo_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($purchase_memos as $po)
                                    @php
                                        $totDppPerMO=0;
                                        $memo_no='';
                                        $purchase_memo_parts = \App\Models\Tx_purchase_memo_part::leftJoin('mst_parts as msp','tx_purchase_memo_parts.part_id','=','msp.id')
                                        ->select(
                                            'tx_purchase_memo_parts.qty',
                                            'tx_purchase_memo_parts.price',
                                            'msp.part_number',
                                            'msp.part_name',
                                        )
                                        ->where([
                                            'tx_purchase_memo_parts.memo_id'=>$po->mo_id,
                                            'tx_purchase_memo_parts.active'=>'Y',
                                        ])
                                        ->orderBy('msp.part_number','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($purchase_memo_parts as $pop)
                                        <tr>
                                            <td>{{ ($supplier_name!=$po->supplier_name)?$po->supplier_name:'' }}</td>
                                            <td>{{ ($memo_no!=$po->memo_no)?$po->memo_no:'' }}</td>
                                            <td style="text-align: center;">{{ $po->memo_date }}</td>
                                            <td>{{ $pop->part_number }}</td>
                                            <td>{{ $pop->part_name }}</td>
                                            <td style="text-align: right;">{{ $pop->qty }}</td>
                                            <td style="text-align: right;">{{ number_format($pop->price,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format(($pop->price*$pop->qty),0,'.',',') }}</td>
                                            <td style="text-align: center;">&nbsp;</td>
                                            <td style="text-align: center;">{{ $po->user_initial }}</td>
                                        </tr>
                                        @php
                                            $totDppPerMO+=($pop->price*$pop->qty);
                                            $supplier_name=$po->supplier_name;
                                            $memo_no=$po->memo_no;
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;font-weight:700;">{{ number_format($totDppPerMO,0,'.',',') }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endisset
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
        $("#purchase-summary-per-branch-per-brand").DataTable({
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
