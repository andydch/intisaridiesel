@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('branch_id')==0){{ 'selected' }}@endif value="0">All</option>
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
                                        <label for="year_id" class="col-sm-3 col-form-label">Year</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $p_Id = (old('year_id')?old('year_id'):(isset($reqs)?$reqs->year_id:0));
                                                @endphp
                                                @for ($y=2023;$y<=date_format(now(),'Y');$y++)
                                                    <option @if ($p_Id==$y) {{ 'selected' }} @endif value="{{ $y }}">{{ $y }}</option>
                                                @endfor
                                            </select>
                                            @error('year_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <input type="button" id="generate-report" class="btn btn-light px-5" value="Generate">
                            <input type="button" id="download-report" class="btn btn-light px-5" value="Download Report">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        @isset($reqs)
                        <table class="table table-striped table-bordered" id="stock-inv-acc-per-branch" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">BRANCH</th>
                                    @php
                                        $date = now();
                                        $month = date_format($date,"m");
                                        if($reqs->year_id<>date_format($date,"Y")){
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
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $branches_f = \App\Models\Mst_branch::where('active','=','Y')
                                    ->when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches_f as $branch)
                                <tr>
                                    <td style="font-weight:bold;">{{ strtoupper($branch->name) }}</td>
                                    @for ($i=1;$i<=$month;$i++)<td>&nbsp;</td>@endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">BEGINING STOCK</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $date_x = date_create($reqs->year_id."-".$i."-01");
                                        date_add($date_x,date_interval_create_from_date_string("-1 months"));

                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => date_format($date_x,"m"),
                                            'rpt_year' => date_format($date_x,"Y"),
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">{{ number_format((($qRpt)?$qRpt->actual_stock:0),0,'.',',') }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">PURCHASE (IN)</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => $i,
                                            'rpt_year' => $reqs->year_id,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">{{ number_format((($qRpt)?$qRpt->purchase_in:0),0,'.',',') }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">SALES (OUT)</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => $i,
                                            'rpt_year' => $reqs->year_id,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">{{ number_format((($qRpt)?$qRpt->sales_out:0),0,'.',',') }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">END STOCK</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => $i,
                                            'rpt_year' => $reqs->year_id,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">
                                        @if (($qRpt))
                                            @if ($qRpt->end_stock>=0)
                                                {{ number_format($qRpt->end_stock,0,'.',',') }}
                                            @else
                                                <span style="color: red;">({{ number_format((($qRpt)?($qRpt->end_stock*-1):0),0,'.',',') }})</span>
                                            @endif
                                        @else
                                            {{ 0 }}
                                        @endif
                                    </td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">ACTUAL STOCK</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => $i,
                                            'rpt_year' => $reqs->year_id,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">{{ number_format((($qRpt)?$qRpt->actual_stock:0),0,'.',',') }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">DIFF STOCK</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::selectRaw('(actual_stock-end_stock) AS diff_stock')
                                        ->where([
                                            'branch_id' => $branch->id,
                                            'rpt_month' => $i,
                                            'rpt_year' => $reqs->year_id,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    <td style="text-align: right;">{{ number_format((($qRpt)?$qRpt->diff_stock:0),0,'.',',') }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    @for ($i=1;$i<=$month;$i++)
                                    <td>&nbsp;</td>
                                    @endfor
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#stock-inv-acc-per-branch").DataTable({
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

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
