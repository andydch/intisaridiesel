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
                                                <option value="#">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:''));
                                                @endphp
                                                <option @if ($p_Id==0){{ 'selected=""' }}@endif value="0">All</option>
                                                @foreach ($branches as $branch)
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
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
                        <table class="table table-striped table-bordered" id="retur-penjualan-detail" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">SUPPLIER NAME</th>
                                    <th style="text-align: center;">INV NO.</th>
                                    <th style="text-align: center;">AMOUNT ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">DATE</th>
                                    <th style="text-align: center;">DUE DATE</th>
                                    <th style="text-align: center;">OVERDUE</th>
                                    <th style="text-align: center;">PIC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $totalAmount = 0;

                                    $timezoneNow = new DateTimeZone('Asia/Jakarta');
                                    $date_local_now = new DateTime();
                                    $date_local_now->setTimeZone($timezoneNow);
                                    // $this->datetimeNow = $date_local_now->format('YmdHisA');

                                    $branches = \App\Models\Mst_branch::when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight:700;">{{ $branch->name }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $supplierName = '';

                                        $q_tx_ro = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                        ->leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
                                        ->select(
                                            'tx_receipt_orders.id as ro_id',
                                            'tx_receipt_orders.receipt_date',
                                            'tx_receipt_orders.invoice_no',
                                            'tx_receipt_orders.invoice_amount',
                                            'm_sp.name as supplier_name',
                                            'm_sp.top',
                                            'userdetails.initial',
                                        )
                                        ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                            // buang RO yg PV detail berstatus full payment dan aktif
                                            $q01->select('receipt_order_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('payment_voucher_id', function ($q01) {
                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                $q01->select('id')
                                                ->from('tx_payment_vouchers')
                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                ->where('active','=','Y');
                                            })
                                            ->where('is_full_payment','=','Y')
                                            ->where('active','=','Y');
                                        })
                                        ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                        ->where([
                                            'tx_receipt_orders.branch_id'=>$branch->id,
                                            'tx_receipt_orders.active'=>'Y',
                                        ])
                                        ->orderBy('tx_receipt_orders.receipt_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($q_tx_ro as $ro)
                                        <tr>
                                            <td>
                                                @if ($supplierName!=$ro->supplier_name)
                                                    @php
                                                        $supplierName = $ro->supplier_name;
                                                    @endphp
                                                    {{ $supplierName }}
                                                @else
                                                    {{ '' }}
                                                @endif
                                            </td>
                                            <td>{{ $ro->invoice_no }}</td>
                                            <td style="text-align: right;">
                                                @php
                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'receipt_order_id'=>$ro->ro_id,
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->sum('total_payment');
                                                    $invoice_amount = $ro->invoice_amount-$notPaidAmount;
                                                @endphp
                                                {{ number_format($invoice_amount,0,'.',',') }}
                                            </td>
                                            <td style="text-align: center;">{{ date_format(date_create($ro->receipt_date),"d/m/Y") }}</td>
                                            @php
                                                $datedue = date_add(date_create($ro->receipt_date), date_interval_create_from_date_string($ro->top." days"));
                                            @endphp
                                            <td style="text-align: center;">{{ date_format($datedue,"d/m/Y") }}</td>
                                            @php
                                                $dtDiff = date_diff($datedue,$date_local_now);
                                            @endphp
                                            <td style="text-align: right;">{{ $dtDiff->format("%a") }}</td>
                                            <td style="text-align: center;">{{ $ro->initial }}</td>
                                        </tr>
                                        @php
                                            $totalAmount += $invoice_amount;
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
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAmount,0,'.',',') }}</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
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
        $("#retur-penjualan-detail").DataTable({
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
