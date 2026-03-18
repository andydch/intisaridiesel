@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
    <style>
        .select2-selection {
            height: 38px !important;
            font-size: 1rem;
        }
    </style>
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.'.$folder.'.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <span for="target_year" class="col-sm-3 col-form-label">Target Year</span>
                                <span for="target_year" class="col-sm-9 col-form-label">{{ $salesman_target->year }}</span>
                            </div>
                            <div class="row mb-3">
                                <span for="" class="col-sm-3 col-form-label">Branch</span>
                                <span for="" class="col-sm-9 col-form-label">{{ $salesman_target->branch->name }}</span>
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $salesman_target->branch_id }}">
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Sales Target</span>
                                <span class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($salesman_target->sales_target,0,'.',',') }}</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totalRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 2%;text-align:center;">#</th>
                                        <th scope="col" style="width: 55%;">Salesman</th>
                                        <th scope="col" style="width: 43%;">Sales Target ({{ $qCurrency->string_val }})</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                @php
                                    $totalSalesTarget = 0;
                                @endphp
                                @if (old('totalRow'))

                                @else
                                    @php
                                        $i=0;
                                    @endphp
                                    @foreach ($salesman_target_detail as $sa_dtl)
                                        <tr id="row{{ $i }}">
                                            <th scope="row" style="text-align:right;">{{ $i+1 }}.</th>
                                            <td>
                                                <label for="" id="salesman_id{{ $i }}">{{ $sa_dtl->salesman->name }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" id="salesman_id{{ $i }}">{{ number_format($sa_dtl->sales_target_per_branch,0,'.',',') }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $totalSalesTarget += $sa_dtl->sales_target_per_branch;
                                        @endphp
                                        @php
                                            $i+=1;
                                        @endphp
                                    @endforeach
                                @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align: right;">Total</td>
                                        <td style="text-align: right;">
                                            <label for="" id="total_sales_target">{{ $qCurrency->string_val.number_format($totalSalesTarget,0,'.',',') }}</label>
                                            <input type="hidden" name="total_sales_target_ori" id="total_sales_target_ori" value="{{ $totalSalesTarget }}">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-custom.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#back-btn").click(function() {
                history.back();
            });
        });
    </script>
@endsection
