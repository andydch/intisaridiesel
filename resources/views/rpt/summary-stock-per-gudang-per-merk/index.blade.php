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
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('branch_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
                                                @endphp
                                                @foreach ($branches as $branch)
                                                    <option @if($p_Id==$branch->id){{ 'selected' }}@endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="brand_id" class="col-sm-3 col-form-label">Brand</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('brand_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('brand_id')?old('brand_id'):(isset($reqs)?$reqs->brand_id:0));
                                                @endphp
                                                @foreach ($brands as $brand)
                                                    <option @if($p_Id==$brand->id){{ 'selected' }}@endif value="{{ $brand->id }}">{{ $brand->title_ind }}</option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Per Date</label>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_start') is-invalid @enderror" maxlength="10"
                                                id="date_start" name="date_start" placeholder="Start Date"
                                                value="@if (old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
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
            {{-- <div class="card" style="display: none;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="inventory-per-branch-per-merk" style="width:100%;">
                            <thead>
                                <th>BRANCH</th>
                                <th>BRAND</th>
                                <th>TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                                <th>TOTAL FINAL PRICE ({{ $qCurrency->string_val }})</th>
                            </thead>
                            @isset($reqs)
                            <tbody>
                                @php
                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->orderBy('name','ASC')
                                    ->get();

                                    $grandTotalAVG=0;
                                    $grandTotalFinalPrice=0;
                                @endphp
                                @foreach ($branches as $branch)
                                    @php
                                        $row=0;
                                        $brands=\App\Models\Mst_global::where([
                                            'data_cat' => 'brand',
                                            'active' => 'Y'
                                        ])
                                        ->when($reqs->brand_id!='0', function($q) use($reqs) {
                                            $q->where('id','=',$reqs->brand_id);
                                        })
                                        ->orderBy('title_ind', 'ASC')
                                        ->get();

                                        $totalAVG=0;
                                        $totalFinalPrice=0;
                                    @endphp
                                    @foreach ($brands as $brand)
                                        @php
                                            $rpts = \App\Models\Tx_qty_part::leftJoin('mst_parts as msp','tx_qty_parts.part_id','=','msp.id')
                                            ->selectRaw('SUM(tx_qty_parts.qty*msp.avg_cost) as total_avg_cost,SUM(tx_qty_parts.qty*msp.final_price) as total_final_price')
                                            ->where('tx_qty_parts.qty','>',0)
                                            ->where([
                                                'tx_qty_parts.branch_id' => $branch->id,
                                                'msp.brand_id' => $brand->id,
                                                'msp.active' => 'Y',
                                            ])
                                            ->first();
                                        @endphp
                                        @if ($rpts)
                                            @if ($rpts->total_avg_cost>0 || $rpts->total_final_price>0)
                                                @if ($row==0)
                                                <tr>
                                                    <th>{{ strtoupper($branch->name) }}</th>
                                                    <th>{{ strtoupper($brand->title_ind) }}</th>
                                                    <td style="text-align: right;">{{ number_format($rpts->total_avg_cost,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format($rpts->total_final_price,0,'.',',') }}</td>
                                                </tr>
                                                @else
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <th>{{ strtoupper($brand->title_ind) }}</th>
                                                    <td style="text-align: right;">{{ number_format($rpts->total_avg_cost,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format($rpts->total_final_price,0,'.',',') }}</td>
                                                </tr>
                                                @endif
                                            @endif
                                            @php
                                                $totalAVG+=$rpts->total_avg_cost;
                                                $totalFinalPrice+=$rpts->total_final_price;
                                            @endphp
                                        @endif
                                        @php
                                            $row+=1;
                                        @endphp
                                    @endforeach
                                    @if ($totalAVG>0 || $totalFinalPrice>0)
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <th style="text-align: right;">{{ number_format($totalAVG,0,'.',',') }}</th>
                                            <th style="text-align: right;">{{ number_format($totalFinalPrice,0,'.',',') }}</th>
                                        </tr>
                                    @endif
                                    @php
                                        $grandTotalAVG+=$totalAVG;
                                        $grandTotalFinalPrice+=$totalFinalPrice;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>GRAND TOTAL</th>
                                    <th style="text-align: right;">{{ number_format($grandTotalAVG,0,'.',',') }}</th>
                                    <th style="text-align: right;">{{ number_format($grandTotalFinalPrice,0,'.',',') }}</th>
                                </tr>
                            </tfoot>
                            @endisset
                        </table>
                    </div>
                </div>
            </div> --}}
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
        $("#inventory-per-branch-per-merk").DataTable({
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

        $(function() {
            $('#date_start').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
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
