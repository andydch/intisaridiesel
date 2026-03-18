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
                    {{-- <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$branch_target->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        @method('PUT') --}}
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <span for="target_year" class="col-sm-3 col-form-label">Target Year</span>
                                    <span for="target_year" class="col-sm-9 col-form-label">{{ $branch_target->year }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span class="col-sm-3 col-form-label">Sales Target</span>
                                    <span class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($branch_target->sales_target,0,'.',',') }}</span>
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
                                            <th scope="col" style="width: 55%;">Branch</th>
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
                                        @foreach ($branch_target_detail as $bt_dtl)
                                            <tr id="row{{ $i }}">
                                                <input type="hidden" name="bt_id{{ $i }}" value="{{ $bt_dtl->id }}">
                                                <th scope="row" style="text-align:right;">{{ $i+1 }}.</th>
                                                <td>
                                                    <label for="" id="total_sales_target">{{ $bt_dtl->branch->name }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" id="total_sales_target">{{ number_format($bt_dtl->sales_target_per_branch,0,'.',',') }}</label>
                                                </td>
                                            </tr>
                                            @php
                                                $i+=1;
                                                $totalSalesTarget += $bt_dtl->sales_target_per_branch;
                                            @endphp
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" style="text-align: right;">Total</td>
                                            <td style="text-align: right;">
                                                <label for="" id="total_sales_target">{{ $qCurrency->string_val.number_format($totalSalesTarget,0,'.',',') }}</label>
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
                    {{-- </form> --}}
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
