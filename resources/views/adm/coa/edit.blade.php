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
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$coas->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="coa_level" class="col-sm-3 col-form-label">COA Level*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('coa_level') is-invalid @enderror" id="coa_level" name="coa_level">
                                        @php
                                            $coa_level = old('coa_level')?old('coa_level'):$coas->coa_level;
                                        @endphp
                                        @for ($i=1;$i<=5;$i++)
                                            <option @if($coa_level==$i){{ 'selected' }}@endif value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('coa_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="coa_parent" class="col-sm-3 col-form-label">COA Parent</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('coa_parent') is-invalid @enderror" id="coa_parent" name="coa_parent">
                                        <option value="0">No Parent</option>
                                        @php
                                            $coa_parent = old('coa_parent')?old('coa_parent'):$coas->coa_parent;
                                        @endphp
                                        @foreach ($qCoaParent as $qCp)
                                            <option @if($coa_parent==$qCp->id){{ 'selected' }}@endif
                                                value="{{ $qCp->id }}">{{ $qCp->coa_code.':'.$qCp->coa_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('coa_parent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="coa_code" class="col-sm-3 col-form-label">COA Code*</label>
                                @if ($coas->is_draft=='Y')
                                    <label for="" class="col-sm-1 col-form-label">Draft</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="coa_code" name="coa_code"
                                            class="form-control @error('coa_code') is-invalid @enderror" maxlength="5"
                                            value="@if (old('coa_code')){{ old('coa_code') }}@else{{ $coas->coa_code }}@endif">
                                        @error('coa_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="col-sm-9">
                                        <input type="text" id="coa_code" name="coa_code"
                                            class="form-control @error('coa_code') is-invalid @enderror" maxlength="5"
                                            value="@if (old('coa_code')){{ old('coa_code') }}@else{{ $coas->coa_code }}@endif">
                                        @error('coa_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="row mb-3">
                                <label for="coa_name" class="col-sm-3 col-form-label">COA Name*</label>
                                <div class="col-sm-9">
                                    <input type="text" id="coa_name" name="coa_name"
                                        class="form-control @error('coa_name') is-invalid @enderror" maxlength="255"
                                        value="@if (old('coa_name')){{ old('coa_name') }}@else{{ $coas->coa_name }}@endif">
                                    @error('coa_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_master_coa" class="col-sm-3 col-form-label">Master COA?</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" id="is_master_coa" name="is_master_coa"
                                        @if(old('is_master_coa')=='on'){{ 'checked' }}@else{{ ($coas->is_master_coa=='Y'?'checked':'') }}@endif>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_balance_sheet" class="col-sm-3 col-form-label">Balance Sheet?</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" id="is_balance_sheet" name="is_balance_sheet"
                                        @if (old('is_balance_sheet')=='on' ){{ 'checked' }}@else{{ ($coas->is_balance_sheet=='Y'?'checked':'') }}@endif>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_profit_loss" class="col-sm-3 col-form-label">Profit Loss?</label>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" id="is_profit_loss" name="is_profit_loss"
                                        @if (old('is_profit_loss')=='on' ){{ 'checked' }}@else{{ ($coas->is_profit_loss=='Y'?'checked':'') }}@endif>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="local_id" class="col-sm-3 col-form-label">Lokal</label>
                                <div class="col-sm-9">
                                    <select {{ ($coas->is_master_coa=='Y' || old('is_master_coa')=='on' ?'disabled':'') }} class="form-select single-select @error('local_id') is-invalid @enderror"
                                        id="local_id" name="local_id">
                                        @php
                                            $local_id = old('local_id')?old('local_id'):$coas->local;
                                        @endphp
                                        <option value="">---</option>
                                        <option @if($local_id=='P'){{ 'selected' }}@endif value="P">PPN</option>
                                        <option @if($local_id=='N'){{ 'selected' }}@endif value="N">Non PPN</option>
                                        <option @if($local_id=='A'){{ 'selected' }}@endif value="A">PPN & Non PPN</option>
                                    </select>
                                    @error('local_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                <div class="col-sm-9">
                                    <select {{ ($coas->is_master_coa=='Y' || old('is_master_coa')=='on' ?'disabled':'') }} class="form-select single-select @error('branch_id') is-invalid @enderror"
                                        id="branch_id" name="branch_id">
                                        <option value="">---</option>
                                        @php
                                            $branch_id = old('branch_id')?old('branch_id'):$coas->branch_id;
                                        @endphp
                                        @foreach ($qBranches as $qB)
                                            <option @if($branch_id==$qB->id){{ 'selected' }}@endif
                                                value="{{ $qB->id }}">{{ $qB->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="beginning_balance_date" class="col-sm-3 col-form-label">Beginning Balance Date</label>
                                <div class="col-sm-9">
                                    <input {{ ($coas->is_master_coa=='Y' || old('is_master_coa')=='on' ?'disabled':'') }} readonly type="text"
                                        class="form-control @error('beginning_balance_date') is-invalid @enderror"
                                        maxlength="10" id="beginning_balance_date" name="beginning_balance_date" placeholder="Date"
                                        value="@if(old('beginning_balance_date')){{ old('beginning_balance_date') }}@else{{ ($coas->beginning_balance_date!=null?date_format(date_create($coas->beginning_balance_date), 'd/m/Y'):'') }}@endif">
                                    @error('beginning_balance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="beginning_balance_amount" class="col-sm-3 col-form-label">Beginning Balance Amount</label>
                                <div class="col-sm-9">
                                    <input {{ ($coas->is_master_coa=='Y' || old('is_master_coa')=='on' ?'disabled':'') }} type="text"
                                        class="form-control @error('beginning_balance_amount') is-invalid @enderror"
                                        maxlength="25" id="beginning_balance_amount" name="beginning_balance_amount" onchange="formatAmount();" placeholder="Amount"
                                        value="@if(old('beginning_balance_amount')){{ old('beginning_balance_amount') }}@else{{ number_format($coas->beginning_balance_amount,2,'.',',') }}@endif">
                                    @error('beginning_balance_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($coas->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save As Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
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
    function formatAmount(){
        let validateChars = '0123456789,.';
        let aMount = $("#beginning_balance_amount").val().replaceAll(',','');
        for(let i=0;i<aMount.length;i++){
            if (validateChars.indexOf(aMount.substr(i, 1))==-1){
                $("#beginning_balance_amount").val(0);
                return false;
            }
        }
        aMount = parseFloat(aMount).numberFormat(2,'.',',');
        $("#beginning_balance_amount").val(aMount);
    }

    $(document).ready(function() {
        $(function() {
            $('#beginning_balance_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD/MM/YYYY',
                time: false
            });
        });
        $("#save-as-draft").click(function() {
            if(!confirm("Data will be saved to database with DRAFT status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            history.back();
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : ($(this).hasClass('w-100') ? '100%' : 'style'),
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });

        $('#is_master_coa').change(function() {
            const isChecked = $('#is_master_coa').is(":checked");
            if (isChecked){
                $('#local_id').attr('disabled', 'disabled');
                $('#branch_id').attr('disabled', 'disabled');

                $('#beginning_balance_date').attr('disabled', 'disabled');
                $('#beginning_balance_date').val('');
                $('#beginning_balance_amount').attr('disabled', 'disabled');
                $('#beginning_balance_amount').val('');
            }else{
                $("#local_id").removeAttr("disabled");
                $("#branch_id").removeAttr("disabled");
                $("#beginning_balance_date").removeAttr("disabled");
                $("#beginning_balance_amount").removeAttr("disabled");
            }
        });

        $('#coa_level').change(function() {
            $("#coa_parent").empty();
            var fd = new FormData();
            fd.append('coa_level', $('#coa_level option:selected').val());
            fd.append('coa_id_not_in', {{ $coas->id }});
            $.ajax({
                url: '{{ url("disp_coa_parent") }}',
                type: 'POST',
                enctype: 'application/x-www-form-urlencoded',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    let o = res[0].coas;
                    let totCoas = o.length;
                    $("#coa_parent").append(`<option value="0">No Parent</option>`);
                    if (totCoas > 0) {
                        for (let i = 0; i < totCoas; i++) {
                            optionText = o[i].coa_code+':'+o[i].coa_name;
                            optionValue = o[i].id;
                            $("#coa_parent").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        });
    });
</script>
@endsection
