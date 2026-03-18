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
                                        <label for="date_start" class="col-sm-3 col-form-label">Tahun</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('period_year') is-invalid @enderror" id="period_year" name="period_year">
                                                @php
                                                    $period_year = (old('period_year')?old('period_year'):(isset($reqs)?$reqs->period_year:0));
                                                @endphp
                                                @for ($year=2023;$year<=date_format(now(),"Y");$year++)
                                                    <option @if ($period_year==$year) {{ 'selected' }} @endif value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                            @error('period_year')
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
                            <input type="button" id="generate-report" class="btn btn-light px-5" value="Generate">
                            <input type="button" id="download-report" class="btn btn-light px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-light px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        @isset($reqs)
                        <table class="table table-striped table-bordered" id="summary-sales-per-branch-per-salesman" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">SALESMAN</th>
                                    @php
                                        $date = now();
                                        $month = date_format($date,"m");
                                        if ($reqs->period_year<date_format($date,"Y")){
                                            $month = 12;
                                        }
                                        $monthNm = '';
                                    @endphp
                                    @for ($i=1;$i<=$month;$i++)
                                        @switch($i)
                                            @case(1)
                                                @php
                                                    $monthNm = 'JAN';
                                                @endphp
                                                @break
                                            @case(2)
                                                @php
                                                    $monthNm = 'FEB';
                                                @endphp
                                                @break
                                            @case(3)
                                                @php
                                                    $monthNm = 'MAR';
                                                @endphp
                                                @break
                                            @case(4)
                                                @php
                                                    $monthNm = 'APR';
                                                @endphp
                                                @break
                                            @case(5)
                                                @php
                                                    $monthNm = 'MAY';
                                                @endphp
                                                @break
                                            @case(6)
                                                @php
                                                    $monthNm = 'JUN';
                                                @endphp
                                                @break
                                            @case(7)
                                                @php
                                                    $monthNm = 'JUL';
                                                @endphp
                                                @break
                                            @case(8)
                                                @php
                                                    $monthNm = 'AUG';
                                                @endphp
                                                @break
                                            @case(9)
                                                @php
                                                    $monthNm = 'SEP';
                                                @endphp
                                                @break
                                            @case(10)
                                                @php
                                                    $monthNm = 'OCT';
                                                @endphp
                                                @break
                                            @case(11)
                                                @php
                                                    $monthNm = 'NOP';
                                                @endphp
                                                @break
                                            @case(12)
                                                @php
                                                    $monthNm = 'DEC';
                                                @endphp
                                                @break
                                            @default
                                                @php
                                                    $monthNm = '';
                                                @endphp
                                        @endswitch
                                        <th style="text-align: center;">{{ $monthNm }}</th>
                                    @endfor
                                    <th style="text-align: center;">TOTAL</th>
                                    <th style="text-align: center;">SALES LAST YEAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totSubTotalAllMonthAllBranch = [0,0,0,0,0,0,0,0,0,0,0,0];
                                    $s_sales_total_all_branch_val = 0;
                                    $s_sales_total_all_branch_lastyear_val = 0;

                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight: bold;">{{ $branch->name }}</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td style="text-align: center;">&nbsp;</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $totSubTotalPerMonthPerBranch = [0,0,0,0,0,0,0,0,0,0,0,0];
                                        $s_sales_total_per_branch_val = 0;
                                        $s_sales_total_per_branch_lastyear_val = 0;

                                        $customers = \App\Models\Mst_customer::leftJoin('userdetails AS user_d','mst_customers.salesman_id','=','user_d.user_id')
                                        ->leftJoin('users','user_d.user_id','=','users.id')
                                        ->select(
                                            'mst_customers.id AS cust_id',
                                            'mst_customers.name AS cust_name',
                                            'users.id AS salesman_id',
                                            'users.name AS salesman_name',
                                            )
                                        ->where([
                                            'mst_customers.active' => 'Y',
                                            'user_d.branch_id' => $branch->id,
                                            'user_d.is_salesman' => 'Y',
                                            'user_d.active' => 'Y',
                                        ])
                                        ->get();
                                    @endphp
                                    @foreach ($customers as $customer)
                                        <tr>
                                            <td>{{ $customer->cust_name }}</td>
                                            @php
                                                $s_target_val = 0;
                                                $s_target_total_val = 0;
                                                $salesman_target = \App\Models\Mst_salesman_target_detail::leftJoin('mst_salesman_targets AS m_target','mst_salesman_target_details.salesman_target_id','=','m_target.id')
                                                ->where([
                                                    'mst_salesman_target_details.salesman_id' => $customer->salesman_id,
                                                    'mst_salesman_target_details.year_per_branch' => $reqs->period_year,
                                                    'mst_salesman_target_details.active' => 'Y',
                                                    'm_target.year' => $reqs->period_year,
                                                    'm_target.branch_id' => $branch->id,
                                                    'm_target.active' => 'Y',
                                                ])
                                                ->first();
                                                if($salesman_target){
                                                    $s_target_val = $salesman_target->sales_target_per_branch/12;
                                                }
                                            @endphp
                                            @for ($i=1;$i<=$month;$i++)
                                                @php
                                                    $s_target_total_val += $s_target_val;
                                                    $totSubTotalPerMonthPerBranch[$i-1] += $s_target_val;
                                                @endphp
                                                <td style="text-align: right;">{{ number_format($s_target_val,0,'.',',') }}</td>
                                            @endfor
                                            <td style="text-align: right;">{{ number_format($s_target_total_val,0,'.',',') }}</td>
                                            @php
                                                // faktur - begin
                                                $fk = \App\Models\Tx_delivery_order::where('delivery_order_no','NOT LIKE','%Draft%')
                                                ->whereRaw('year(delivery_order_date)='.($reqs->period_year-1))
                                                ->where([
                                                    'customer_id' => $customer->cust_id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('total_before_vat');
                                                // faktur - end

                                                // nota penjualan - begin
                                                $np = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                                ->whereRaw('year(delivery_order_date)='.($reqs->period_year-1))
                                                ->where([
                                                    'customer_id' => $customer->cust_id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('total_price');
                                                // nota penjualan - end

                                                $all_sales = $fk+$np;
                                                $s_sales_total_per_branch_lastyear_val += $all_sales;
                                            @endphp
                                            <td style="text-align: right;">{{ number_format($all_sales,0,'.',',') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>SUB TOTAL</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            @php
                                                $s_sales_total_per_branch_val += $totSubTotalPerMonthPerBranch[$i-1];
                                                $totSubTotalAllMonthAllBranch[$i-1] += $totSubTotalPerMonthPerBranch[$i-1];
                                                $s_sales_total_all_branch_val += $totSubTotalPerMonthPerBranch[$i-1];
                                                $s_sales_total_all_branch_lastyear_val += $s_sales_total_per_branch_lastyear_val;
                                            @endphp
                                            <td style="text-align: right;">{{ number_format($totSubTotalPerMonthPerBranch[$i-1],0,'.',',') }}</td>
                                        @endfor
                                        <td style="text-align: right;">{{ number_format($s_sales_total_per_branch_val,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($s_sales_total_per_branch_lastyear_val,0,'.',',') }}</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td style="text-align: center;">&nbsp;</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="font-weight: bold;">TOTAL</td>
                                    @for ($i=1;$i<=$month;$i++)
                                        <td style="text-align: right;">{{ number_format($totSubTotalAllMonthAllBranch[$i-1],0,'.',',') }}</td>
                                    @endfor
                                    <td style="text-align: right;">{{ number_format($s_sales_total_all_branch_val,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format($s_sales_total_all_branch_lastyear_val,0,'.',',') }}</td>
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
        $("#summary-sales-per-branch-per-salesman").DataTable({
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
