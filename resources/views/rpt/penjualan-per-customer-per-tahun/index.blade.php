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
                                    <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if (old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
                                            @error('lokal_input')
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
                        <table class="table table-striped table-bordered" id="sales-per-faktur-per-sales-order" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">NAME CUST</th>
                                    <th style="text-align: center;">SALESMAN</th>
                                    @php
                                        $date = now();
                                        $month = date_format($date,"m");
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
                                    <th style="text-align: center;">GP</th>
                                    <th style="text-align: center;">TARGET</th>
                                    <th style="text-align: center;">ACHV%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $gTotal01 = 0;
                                    $gTotal02 = 0;
                                    $gTotal03 = 0;
                                    $gTotal04 = 0;
                                    $gTotal05 = 0;
                                    $gTotal06 = 0;
                                    $gTotal07 = 0;
                                    $gTotal08 = 0;
                                    $gTotal09 = 0;
                                    $gTotal10 = 0;
                                    $gTotal11 = 0;
                                    $gTotal12 = 0;
                                    $gTotalAllBranch = 0;

                                    $qBranch = \App\Models\Mst_branch::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qBranch as $qB)
                                    <tr>
                                        <td style="text-align: center;font-weight:bold;">{{ $qB->name }}</td>
                                        <td>&nbsp;</td>
                                        @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                            <td>&nbsp;</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $total01 = 0;
                                        $total02 = 0;
                                        $total03 = 0;
                                        $total04 = 0;
                                        $total05 = 0;
                                        $total06 = 0;
                                        $total07 = 0;
                                        $total08 = 0;
                                        $total09 = 0;
                                        $total10 = 0;
                                        $total11 = 0;
                                        $total12 = 0;
                                        $totalPerBranch = 0;

                                        $qFkNp = \App\Models\V_faktur_dan_nota_penjualan::select(
                                            'customer_id',
                                            'customer_name',
                                            'created_by',
                                            )
                                        ->where('branch_id','=',$qB->id)
                                        ->orderBy('delivery_order_date','DESC')
                                        ->orderBy('customer_name','ASC')
                                        ->groupBy('customer_id')
                                        ->groupBy('customer_name')
                                        ->groupBy('created_by')
                                        ->get();
                                    @endphp
                                    @foreach ($qFkNp as $qFN)
                                        <tr>
                                            <td>{{ $qFN->customer_name }}</td>
                                            <td style="text-align: center;">{{ $qFN->salesman->userDetail->initial }}</td>
                                            @php
                                                $totalPrice = 0;
                                            @endphp
                                            @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                                <td style="text-align: right;">
                                                @php
                                                    $qFkNpTotal = \App\Models\V_faktur_dan_nota_penjualan::where([
                                                        'branch_id' => $qB->id,
                                                        'customer_id' => $qFN->customer_id,
                                                        'created_by' => $qFN->created_by,
                                                    ])
                                                    ->whereRaw('month(delivery_order_date)='.$iMonth.' AND year(delivery_order_date)='.$reqs->period_year)
                                                    ->sum('total_price');
                                                    $totalPrice += $qFkNpTotal;
                                                @endphp
                                                {{ number_format($qFkNpTotal,0,'.',',') }}
                                                </td>
                                                @switch($iMonth)
                                                    @case(1)
                                                        @php
                                                            $total01 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(2)
                                                        @php
                                                            $total02 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(3)
                                                        @php
                                                            $total03 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(4)
                                                        @php
                                                            $total04 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(5)
                                                        @php
                                                            $total05 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(6)
                                                        @php
                                                            $total06 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(7)
                                                        @php
                                                            $total07 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(8)
                                                        @php
                                                            $total08 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(9)
                                                        @php
                                                            $total09 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(10)
                                                        @php
                                                            $total10 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(11)
                                                        @php
                                                            $total11 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @case(12)
                                                        @php
                                                            $total12 += $qFkNpTotal;
                                                        @endphp
                                                        @break
                                                    @default
                                                @endswitch
                                            @endfor
                                            <td style="text-align: right;">{{ number_format($totalPrice,0,'.',',') }}</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                            @switch($iMonth)
                                                @case(1)
                                                    <td style="text-align: right;">{{ number_format($total01,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal01 += $total01;
                                                    @endphp
                                                    @break
                                                @case(2)
                                                    <td style="text-align: right;">{{ number_format($total02,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal02 += $total02;
                                                    @endphp
                                                    @break
                                                @case(3)
                                                    <td style="text-align: right;">{{ number_format($total03,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal03 += $total03;
                                                    @endphp
                                                    @break
                                                @case(4)
                                                    <td style="text-align: right;">{{ number_format($total04,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal04 += $total04;
                                                    @endphp
                                                    @break
                                                @case(5)
                                                    <td style="text-align: right;">{{ number_format($total05,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal05 += $total05;
                                                    @endphp
                                                    @break
                                                @case(6)
                                                    <td style="text-align: right;">{{ number_format($total06,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal06 += $total06;
                                                    @endphp
                                                    @break
                                                @case(7)
                                                    <td style="text-align: right;">{{ number_format($total07,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal07 += $total07;
                                                    @endphp
                                                    @break
                                                @case(8)
                                                    <td style="text-align: right;">{{ number_format($total08,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal08 += $total08;
                                                    @endphp
                                                    @break
                                                @case(9)
                                                    <td style="text-align: right;">{{ number_format($total09,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal09 += $total09;
                                                    @endphp
                                                    @break
                                                @case(10)
                                                    <td style="text-align: right;">{{ number_format($total10,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal10 += $total10;
                                                    @endphp
                                                    @break
                                                @case(11)
                                                    <td style="text-align: right;">{{ number_format($total11,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal11 += $total11;
                                                    @endphp
                                                    @break
                                                @case(12)
                                                    <td style="text-align: right;">{{ number_format($total12,0,'.',',') }}</td>
                                                    @php
                                                        $gTotal12 += $total12;
                                                    @endphp
                                                    @break
                                                @default
                                            @endswitch
                                        @endfor
                                        <td style="text-align: right;">
                                        @php
                                            $totalPerBranch = $total01+$total02+$total03+$total04+$total05+$total06+$total07+$total08+
                                                $total09+$total10+$total11+$total12;
                                        @endphp
                                        {{ number_format($totalPerBranch,0,'.',',') }}
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                            <td>&nbsp;</td>
                                        @endfor
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="text-align: center;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                        @switch($iMonth)
                                            @case(1)
                                                <td style="text-align: right;">{{ number_format($gTotal01,0,'.',',') }}</td>
                                                @break
                                            @case(2)
                                                <td style="text-align: right;">{{ number_format($gTotal02,0,'.',',') }}</td>
                                                @break
                                            @case(3)
                                                <td style="text-align: right;">{{ number_format($gTotal03,0,'.',',') }}</td>
                                                @break
                                            @case(4)
                                                <td style="text-align: right;">{{ number_format($gTotal04,0,'.',',') }}</td>
                                                @break
                                            @case(5)
                                                <td style="text-align: right;">{{ number_format($gTotal05,0,'.',',') }}</td>
                                                @break
                                            @case(6)
                                                <td style="text-align: right;">{{ number_format($gTotal06,0,'.',',') }}</td>
                                                @break
                                            @case(7)
                                                <td style="text-align: right;">{{ number_format($gTotal07,0,'.',',') }}</td>
                                                @break
                                            @case(8)
                                                <td style="text-align: right;">{{ number_format($gTotal08,0,'.',',') }}</td>
                                                @break
                                            @case(9)
                                                <td style="text-align: right;">{{ number_format($gTotal09,0,'.',',') }}</td>
                                                @break
                                            @case(10)
                                                <td style="text-align: right;">{{ number_format($gTotal10,0,'.',',') }}</td>
                                                @break
                                            @case(11)
                                                <td style="text-align: right;">{{ number_format($gTotal11,0,'.',',') }}</td>
                                                @break
                                            @case(12)
                                                <td style="text-align: right;">{{ number_format($gTotal12,0,'.',',') }}</td>
                                                @break
                                            @default
                                        @endswitch
                                    @endfor
                                    <td style="text-align: right;">
                                    @php
                                        $gTotalAllBranch = $gTotal01+$gTotal02+$gTotal03+$gTotal04+$gTotal05+$gTotal06+$gTotal07+$gTotal08+
                                            $gTotal09+$gTotal10+$gTotal11+$gTotal12;
                                    @endphp
                                    {{ number_format($gTotalAllBranch,0,'.',',') }}
                                    </td>
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
        $("#sales-per-faktur-per-sales-order").DataTable({
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
