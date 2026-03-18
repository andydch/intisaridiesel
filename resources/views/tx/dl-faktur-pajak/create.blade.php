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
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-xl-9">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="start_date" class="col-sm-2 col-form-label">Start Date*</label>
                                                <div class="col-sm-3">
                                                    <input readonly type="text" class="form-control @error('start_date') is-invalid @enderror"
                                                        maxlength="10" id="start_date" name="start_date" placeholder="start date"
                                                        value="@if(old('start_date')){{ old('start_date') }}@endif">
                                                    @error('start_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <label for="end_date" class="col-sm-2 col-form-label">End Date*</label>
                                                <div class="col-sm-3">
                                                    <input readonly type="text" class="form-control @error('end_date') is-invalid @enderror"
                                                        maxlength="10" id="end_date" name="end_date" placeholder="end date"
                                                        value="@if(old('end_date')){{ old('end_date') }}@endif">
                                                    @error('end_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_all" class="col-sm-2 col-form-label">Customer</label>
                                                <div class="col-sm-3">
                                                    @php
                                                        $custAll = (old('customer_all')?(old('customer_all')=='on'?'checked':''):'');
                                                    @endphp
                                                    <input type="checkbox" class="form-check-input @error('customer_all') is-invalid @enderror"
                                                        id="customer_all" name="customer_all" {{ $custAll }}>&nbsp;All Customer
                                                    @error('customer_all')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_one" class="col-sm-2 col-form-label">Customer</label>
                                                <div class="col-sm-4">
                                                    <select class="form-select single-select @error('customer_one') is-invalid @enderror"
                                                        id="customer_one" name="customer_one">
                                                        <option value="#">Choose...</option>
                                                        @if (old('start_date'))
                                                            @php
                                                                $start_date = explode("/", old('start_date'));
                                                                $end_date = explode("/", old('end_date'));

                                                                $custs = \App\Models\Mst_customer::whereIn('id', function($q) use($start_date,$end_date){
                                                                    $q->select('customer_id')
                                                                    ->from('tx_delivery_orders')
                                                                    ->whereRaw('delivery_order_no NOT LIKE \'%Draft%\'')
                                                                    ->whereRaw('delivery_order_date>=\''.$start_date[2].'-'.$start_date[1].'-'.$start_date[0].'\'
                                                                        AND delivery_order_date<=\''.$end_date[2].'-'.$end_date[1].'-'.$end_date[0].'\'')
                                                                    ->where([
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->orderBy('delivery_order_date','DESC')
                                                                    ->orderBy('created_at','DESC');
                                                                })
                                                                ->where([
                                                                    'active'=>'Y',
                                                                ])
                                                                ->orderBy('name','ASC')
                                                                ->get();
                                                            @endphp
                                                            @foreach ($custs as $cust)
                                                                <option value="{{ $cust->id }}">{{ $cust->customer_unique_code.' - '.$cust->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @error('customer_one')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="button" id="select-cust" class="btn btn-primary px-5" onclick="addRowCust();" value="Pilih Customer">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <input type="hidden" name="totRowCust" id="totRowCust" value="0">
                                                <label for="" class="col-sm-2 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <table id="cust-list-few" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 94%;text-align:center;">Customer</th>
                                                                <th scope="col" style="width: 3%;text-align:center;">Delete</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-cust">
                                                            @if (old('totRowCust'))
                                                                @for ($row=0;$row<old('totRowCust');$row++)
                                                                    <tr id="rowCust{{ $row }}">
                                                                        <td>
                                                                            @php
                                                                                $cust = \App\Models\Mst_customer::where([
                                                                                    'id'=>old('custId'.$row),
                                                                                ])
                                                                                ->first();
                                                                                if ($cust){
                                                                                    echo '<label for="" name="custName{{ $row }}" id="custName{{ $row }}"
                                                                                        class="col-form-label">'.$cust->name.'</label>';
                                                                                }
                                                                            @endphp
                                                                            <input type="hidden" name="custId{{ $row }}" id="custId{{ $row }}" value="{{ old('custId'.$row) }}" />
                                                                        </td>
                                                                        <td>
                                                                            @php
                                                                                $checked = '';
                                                                                if (old('del_customer'.$row)){
                                                                                    $checked = 'checked';
                                                                                }
                                                                            @endphp
                                                                            <input {{ $checked }} type="checkbox" name="del_customer{{ $row }}" id="del_customer{{ $row }}"
                                                                                style="margin: auto;display:block;"/>
                                                                        </td>
                                                                    </tr>
                                                                @endfor
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                    <input type="button" id="del-row-cust" class="btn btn-danger px-5" value="Remove Row" style="margin-top: 5px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="download-btn" class="btn btn-primary px-5" value="Download">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#header,#footer',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
    });
</script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function getCust(){
        var fd = new FormData();
        fd.append('start_date', $("#start_date").val());
        fd.append('end_date', $("#end_date").val());
        $.ajax({
            url: "{{ url('/disp_custperfk') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].custs;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].customer_unique_code+' - '+o[i].name;
                        optionValue = o[i].id;
                        $("#customer_one").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function addRowCust(){
        let cust_id = $("#customer_one option:selected").val();
        let cust_name = $("#customer_one option:selected").text();
        let lastCounter = parseInt($("#totRowCust").val());

        for(let i=0;i<$("#totRowCust").val();i++){
            if($("#custName"+i).text()===cust_name){
                alert('The Customer Name already exists.');
                return false;
            }
        }
        if(cust_id==='#'){
            return false;
        }

        vHtml = '<tr id="rowCust'+lastCounter+'">'+
            '<td>'+
            '<label for="" name="custName'+lastCounter+'" id="custName'+lastCounter+'" class="col-form-label">'+cust_name+'</label>'+
            '<input type="hidden" name="custId'+lastCounter+'" id="custId'+lastCounter+'" value="'+cust_id+'">'+
            '</td>'+
            '<td style="text-align: center;"><input type="checkbox" id="del_customer'+lastCounter+'" name="del_customer'+lastCounter+'"></td>'+
            '</tr>';
        $("#new-row-cust").append(vHtml);
        $("#totRowCust").val(parseInt(lastCounter)+1);
    }

    $(document).ready(function() {
        $(function() {
            $('#start_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#end_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD/MM/YYYY',
                time: false
            });
        });

        $("#start_date").change(function() {
            $("#customer_one").empty();
            $("#customer_one").append(`<option value="#">Choose...</option>`);
            $("#new-row-cust").empty();

            if($("#start_date").val()!=='' && $("#end_date").val()!==''){
                getCust();
            }
        });

        $("#end_date").change(function() {
            $("#customer_one").empty();
            $("#customer_one").append(`<option value="#">Choose...</option>`);
            $("#new-row-cust").empty();

            if($("#start_date").val()!=='' && $("#end_date").val()!==''){
                getCust();
            }
        });

        $("#download-btn").click(function() {
            $("#submit-form").submit();
        });

        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/faktur') }}";
        });

        $("#del-row-cust").click(function() {
            for (let i=0; i<$("#totRowCust").val(); i++) {
                if ($("#del_customer"+i).is(':checked')) {
                    $("#rowCust"+i).remove();
                }
            }
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
