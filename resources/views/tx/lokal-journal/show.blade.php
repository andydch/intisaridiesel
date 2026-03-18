@extends('layouts.app')

@section('style')
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
    .part-id {
        font-size: large !important;
        font-weight: 700;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                {{-- @if($errors->any())
                Error:
                {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                @endif --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Lokal Journal No</label>
                            <label for="" class="col-sm-9 col-form-label part-id">{{ $journals->general_journal_no }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Journal Date</label>
                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($journals->general_journal_date), 'd/m/Y') }}</label>
                        </div>
                        @php
                            $is_cash = '0';
                            $lastCoa = 0;
                        @endphp
                        @if (strpos("-".$journals->module_no,env('P_PAYMENT_VOUCHER'))>0)
                            @php
                                $qPV = \App\Models\Tx_payment_voucher::where([
                                    'payment_voucher_no'=>$journals->module_no,
                                ])
                                ->first();
                                if ($qPV){
                                    $is_cash = $qPV->payment_mode;
                                    $lastCoa = 5;
                                }
                            @endphp
                        @endif
                        @if (strpos("-".$journals->module_no,env('P_PAYMENT_RECEIPT'))>0)
                            @php
                                $qPV = \App\Models\Tx_payment_voucher::where([
                                    'payment_voucher_no'=>$journals->module_no,
                                ])
                                ->first();
                                if ($qPV){
                                    $is_cash = $qPV->payment_mode;
                                    $lastCoa = 4;
                                }
                            @endphp
                        @endif
                    </div>
                </div>
                <hr>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        @php
                            $totRow = $totalRow;
                        @endphp
                        <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr style="width: 100%;">
                                    <th scope="col" style="width: 3%;text-align:center;">#</th>
                                    <th scope="col" style="width: 25%;">COA Code</th>
                                    <th scope="col" style="width: 25%;">Description</th>
                                    <th scope="col" style="width: 15%;">Debet ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 15%;">Credit ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $totalDebet = 0;
                                    $totalKredit = 0;
                                    $i = 0;
                                    $j = 0;
                                @endphp
                                @if ($is_cash==1)
                                    @foreach ($journaldtls as $dtls)
                                        @if ($dtls->coa)
                                            {{-- @if ($dtls->debit!=0 || $dtls->kredit!=0) --}}
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" class="col-form-label">{{ $j+1 }}.</label>
                                                        <input type="hidden" name="gj_dtl_id{{ $i }}" value="{{ $dtls->id }}">
                                                    </th>
                                                    <td><label for="" class="col-form-label">{{ ($dtls->coa?$dtls->coa->coa_code_complete.' - '.$dtls->coa->coa_name:'') }}</label></td>
                                                    <td><label for="" class="col-form-label">{{ $dtls->description }}</label></td>
                                                    <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($dtls->debit,0,'.',',') }}</label></td>
                                                    <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($dtls->kredit,0,'.',',') }}</label></td>
                                                </tr>
                                                @php
                                                    $totalDebet += $dtls->debit;
                                                    $totalKredit += $dtls->kredit;
                                                    $j += 1;
                                                @endphp
                                            {{-- @endif --}}
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endif
                                    @endforeach
                                @else
                                    @foreach ($journaldtls as $dtls)
                                        @if ($dtls->coa)
                                            @if ($dtls->debit!=0 || $dtls->kredit!=0)
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" class="col-form-label">{{ $i+1 }}.</label>
                                                        <input type="hidden" name="gj_dtl_id{{ $i }}" value="{{ $dtls->id }}">
                                                    </th>
                                                    <td><label for="" class="col-form-label">{{ ($dtls->coa?$dtls->coa->coa_code_complete.' - '.$dtls->coa->coa_name:'') }}</label></td>
                                                    <td><label for="" class="col-form-label">{{ $dtls->description }}</label></td>
                                                    <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($dtls->debit,0,'.',',') }}</label></td>
                                                    <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($dtls->kredit,0,'.',',') }}</label></td>
                                                </tr>
                                                @php
                                                    $totalDebet += $dtls->debit;
                                                    if ($dtls->kredit>0){
                                                        $totalKredit = $dtls->kredit;
                                                    }
                                                    $i += 1;
                                                @endphp
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><label for="">Total</label></td>
                                    <td style="text-align: right;">
                                        <label for="" id="lbl-total-debet">{{ $qCurrency->string_val.number_format($totalDebet,0,'.',',') }}</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" id="lbl-total-credit">{{ $qCurrency->string_val.number_format($totalDebet,0,'.',',') }}</label>
                                    </td>
                                </tr>
                                @error('totalCredit')
                                    <tr>
                                        <td colspan="6" style="margin-top: .25rem;color: #f41127;font-size: .875em;">{{ $message }}</td>
                                    </tr>
                                @enderror
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
    </div>
</div>
<!--end row-->
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
