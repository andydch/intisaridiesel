@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}

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
            <div class="row">
            <form id="submitApproval" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$journals->id) }}"
                method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    @php
                        $disabled = '';
                    @endphp
                    @if ($journals->status_appr=='Y')
                        @php
                            $disabled = 'disabled';
                        @endphp
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">General Journal No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $journals->general_journal_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Transaction Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($journals->general_journal_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">Approval Status</label>
                                <div class="col-sm-9">
                                    @if ($journals->is_wt_for_appr=='Y')
                                        {{ 'Waiting for Approval' }}
                                    @else
                                        @if ($journals->status_appr=='Y')
                                            {{ 'Approved' }}
                                        @endif
                                        @if ($journals->status_appr=='N')
                                            {{ 'Rejected' }}
                                        @endif
                                        @if ($journals->status_appr!=null)
                                            {!! ' by '.$journals->approvedBy->name.' at '.date_format(date_create($journals->approved_at), 'd M Y H:i:s').' <i>(server time)</i>' !!}
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">&nbsp;</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('journal_appr') is-invalid @enderror" id="journal_appr" name="journal_appr">
                                        @if ($journals->status_appr=='N' || is_null($journals->status_appr))
                                            <option @if (old('journal_appr')=='Y'){{'selected'}}@endif value="Y">Approve</option>
                                        @endif
                                        {{-- @if ($journals->approved_status=='A' || is_null($journals->approved_status))
                                            <option @if (old('journal_appr')=='R'){{'selected'}}@endif value="N">Reject</option>
                                        @endif --}}
                                    </select>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                                        <th scope="col" style="width: 17%;">COA Code</th>
                                        <th scope="col" style="width: 20%;">Description</th>
                                        <th scope="col" style="width: 15%;">Debet ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Credit ({{ $qCurrency->string_val }})</th>
                                        {{-- <th scope="col" style="width: 15%;">New Debet ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">New Credit ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Old Debet ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Old Credit ({{ $qCurrency->string_val }})</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $totalDebet = 0;
                                        $totalKredit = 0;
                                        $i = 0;
                                    @endphp
                                    @foreach ($journaldtls as $dtls)
                                        <tr id="row{{ $i }}">
                                            <th scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label">{{ $i+1 }}.</label>
                                                <input type="hidden" name="gj_dtl_id{{ $i }}" value="{{ $dtls->id }}">
                                            </th>
                                            <td><label for="" class="col-form-label">{{ $dtls->coa->coa_code_complete.' - '.$dtls->coa->coa_name }}</label></td>
                                            <td><label for="" class="col-form-label">{{ $dtls->description }}</label></td>
                                            <td style="text-align: right;"><label for="" class="col-form-label">{{ ($dtls->debit>0?number_format($dtls->debit,0,'.',','):'') }}</label></td>
                                            <td style="text-align: right;"><label for="" class="col-form-label">{{ ($dtls->kredit>0?number_format($dtls->kredit,0,'.',','):'') }}</label></td>
                                            {{-- <td style="text-align: right;"><label for="" class="col-form-label">{{ ($dtls->debit>0?number_format($dtls->debit_old,0,'.',','):'') }}</label></td>
                                            <td style="text-align: right;"><label for="" class="col-form-label">{{ ($dtls->kredit>0?number_format($dtls->kredit_old,0,'.',','):'') }}</label></td> --}}
                                        </tr>
                                        @php
                                            $totalDebet += $dtls->debit;
                                            $totalKredit += $dtls->kredit;
                                            $i += 1;
                                        @endphp
                                    @endforeach
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
                                    <input {{ $disabled }} type="submit" id="complexConfirm" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
