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
                        <table class="table table-striped table-bordered" id="summary-sales-per-branch-per-salesman" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">BRANCH</th>
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
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight: bold;">{{ $branch->name }}</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td>&nbsp;</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>SALES TARGET</td>
                                        @php
                                            $target = 0;
                                            $salesTargets = \App\Models\Mst_branch_target_detail::where([
                                                'branch_id' => $branch->id,
                                                'year_per_branch' => $reqs->period_year,
                                                'active' => 'Y',
                                            ])
                                            ->first();
                                            if($salesTargets){
                                                $target = $salesTargets->sales_target_per_branch;
                                            }
                                        @endphp
                                        @for ($i=1;$i<=$month;$i++)
                                            <td style="text-align: right;">{{ number_format(($target/12),0,'.',',') }}</td>
                                        @endfor
                                        <td style="text-align: right;">{{ number_format($target,0,'.',',') }}</td>
                                    </tr>
                                    <tr>
                                        <td>SALES ACTUAL</td>
                                        @php
                                            $totalSalesActual = 0;
                                            $arrSalesActual = [0,0,0,0,0,0,0,0,0,0,0,0];
                                        @endphp
                                        @for ($i=1;$i<=$month;$i++)
                                            @php
                                                $all_sales = 0;
                                                $total_before_vat = 0;
                                                $total_price = 0;

                                                // faktur - begin
                                                $fk = \App\Models\Tx_delivery_order::leftJoin('userdetails as usr','tx_delivery_orders.created_by','=','usr.user_id')
                                                ->select('tx_delivery_orders.total_before_vat')
                                                ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%')
                                                ->whereRaw('month(DATE_ADD(tx_delivery_orders.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$i)
                                                ->whereRaw('year(DATE_ADD(tx_delivery_orders.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$reqs->period_year)
                                                ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_delivery_orders.branch_id IS null) OR tx_delivery_orders.branch_id='.$branch->id.')')
                                                ->where([
                                                    'tx_delivery_orders.active' => 'Y',
                                                    // 'created_by' => $sales->user_id,
                                                ])
                                                ->first();
                                                if($fk){
                                                    $total_before_vat = $fk->total_before_vat;
                                                }
                                                // faktur - end

                                                // nota penjualan - begin
                                                $np = \App\Models\Tx_delivery_order_non_tax::leftJoin('userdetails as usr','tx_delivery_order_non_taxes.created_by','=','usr.user_id')
                                                ->select('tx_delivery_order_non_taxes.total_price')
                                                ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%')
                                                ->whereRaw('month(DATE_ADD(tx_delivery_order_non_taxes.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$i)
                                                ->whereRaw('year(DATE_ADD(tx_delivery_order_non_taxes.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$reqs->period_year)
                                                ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_delivery_order_non_taxes.branch_id IS null) OR tx_delivery_order_non_taxes.branch_id='.$branch->id.')')
                                                ->where([
                                                    'tx_delivery_order_non_taxes.active' => 'Y',
                                                    // 'created_by' => $sales->user_id,
                                                ])
                                                ->first();
                                                if($np){
                                                    $total_price = $np->total_price;
                                                }
                                                // nota penjualan - end

                                                $all_sales = $total_before_vat+$total_price;
                                                $totalSalesActual += $all_sales;
                                                $arrSalesActual[$i-1] = $all_sales;
                                            @endphp
                                            <td style="text-align: right;">{{ number_format(($all_sales),0,'.',',') }}</td>
                                        @endfor
                                        <td style="text-align: right;">{{ number_format(($totalSalesActual),0,'.',',') }}</td>
                                    </tr>
                                    <tr>
                                        <td>ACHIEVEMENT</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td style="text-align: right;">{{ number_format(($target>0)?($arrSalesActual[$i-1]/($target/12))*100:0,0,'.',',') }}%</td>
                                        @endfor
                                        <td style="text-align: right;">{{ number_format(($target>0)?($totalSalesActual/($target))*100:0,0,'.',',') }}%</td>
                                    </tr>
                                    <tr>
                                        <td>END STOCK</td>
                                        @php
                                            $arrEndStock = [0,0,0,0,0,0,0,0,0,0,0,0];
                                        @endphp
                                        @for ($i=1;$i<=$month;$i++)
                                            @php
                                                $endStock = 0;
                                                $invStock = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                                    'branch_id' => $branch->id,
                                                    'rpt_month' => $i,
                                                    'rpt_year' => $reqs->period_year,
                                                    'active' => 'Y',
                                                ])
                                                ->first();
                                                if($invStock){
                                                    $endStock = $invStock->end_stock;
                                                }
                                                $arrEndStock[$i-1] = $endStock;
                                            @endphp
                                            <td style="text-align: right;">{{ number_format($endStock,0,'.',',') }}</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>AGE OF STOCK</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td style="text-align: right;">{{ number_format(($arrSalesActual[$i-1]>0)?(($arrEndStock[$i-1]/$arrSalesActual[$i-1])*100):0,2,'.',',') }}</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        @for ($i=1;$i<=$month;$i++)
                                            <td>&nbsp;</td>
                                        @endfor
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
