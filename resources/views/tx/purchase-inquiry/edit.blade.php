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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($p_inquiries->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Purchase Inquiry No</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $p_inquiries->purchase_inquiry_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Purchase Inquiry Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($p_inquiries->purchase_inquiry_date), 'd/m/Y') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                    <div class="col-sm-9">
                                        <select
                                            class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                            id="supplier_id" name="supplier_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierId = (old('supplier_id')?old('supplier_id'):$p_inquiries->supplier_id);
                                            @endphp
                                            @foreach ($suppliers as $p)
                                                <option @if ($supplierId==$p->id) {{ 'selected' }} @endif
                                                    value="{{ $p->id }}">{{ (!is_null($p->entity_type)?$p->entity_type->title_ind:'').' '.$p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div id="supplier_data" class="row mb-3">
                                    <label for="supplier_data" class="col-sm-3 col-form-label">Information</label>
                                    <div id="supplier_info" class="col-sm-9">
                                        @if(old('supplier_id') || @isset($supplierPics[0]))
                                            {!!
                                            (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind.' ':'').$supplierPics[0]->name.
                                            '<br />Address: '.$supplierPics[0]->office_address.
                                            ($supplierPics[0]->subdistrict->sub_district_name=='Other'?'':
                                            ', '.ucwords(strtolower($supplierPics[0]->subdistrict->sub_district_name))).
                                            ($supplierPics[0]->district->district_name=='Other'?'':
                                            ', '.$supplierPics[0]->district->district_name).
                                            ($supplierPics[0]->city->city_name=='Other'?'':
                                            '<br />'.($supplierPics[0]->city->city_type=='Luar
                                            Negeri'?'':$supplierPics[0]->city->city_type).' '.
                                            $supplierPics[0]->city->city_name).
                                            ($supplierPics[0]->province->province_name=='Other'?'':
                                            '<br />'.$supplierPics[0]->province->province_name).
                                            '<br />'.$supplierPics[0]->country->country_name.
                                            ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                            ' '.$supplierPics[0]->subdistrict->post_code)
                                            !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="supplier_pic" class="col-sm-3 col-form-label">PIC*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('supplier_pic') is-invalid @enderror"
                                            id="supplier_pic" name="supplier_pic">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierPic = (old('supplier_pic')?old('supplier_pic'):$p_inquiries->pic_idx);
                                            @endphp
                                            @foreach ($supplierPics as $p)
                                                <option @if ($supplierPic==1) {{ 'selected' }} @endif value="1">{{ $p->pic1_name }}</option>
                                                @if (!is_null($p->pic2_name))
                                                    <option @if ($supplierPic==2) {{ 'selected' }} @endif value="2">{{ $p->pic2_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('supplier_pic')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="header_txt" class="col-sm-3 col-form-label">Header*</label>
                                    <div class="col-sm-9">
                                        <textarea name="header_txt" id="header_txt" rows="3" class="form-control @error('header_txt') is-invalid @enderror"
                                            style="width:100%;">@if (old('header_txt')){{ old('header_txt') }}@else{{ $p_inquiries->header }}@endif</textarea>
                                        @error('header_txt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="footer_txt" class="col-sm-3 col-form-label">Footer*</label>
                                    <div class="col-sm-9">
                                        <textarea name="footer_txt" id="footer_txt" rows="3" class="form-control @error('footer_txt') is-invalid @enderror"
                                            style="width:100%;">@if (old('footer_txt')){{ old('footer_txt') }}@else{{ $p_inquiries->footer }}@endif</textarea>
                                        @error('footer_txt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="remark_txt" class="col-sm-3 col-form-label">Remark</label>
                                    <div class="col-sm-9">
                                        <textarea name="remark_txt" id="remark_txt" rows="3" class="form-control @error('remark_txt') is-invalid @enderror"
                                            style="width:100%;">@if (old('remark_txt')){{ old('remark_txt') }}@else{{ $p_inquiries->remark }}@endif</textarea>
                                        @error('remark_txt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
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
                                        <th scope="col" style="width: 35%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Unit</th>
                                        <th scope="col" style="width: 40%;">Description</th>
                                        <th scope="col" style="width: 2%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if (old('totalRow'))
                                        @for ($i = 0; $i < $totRow; $i++)
                                            {{-- @if (old('part_name_'.$i)) --}}
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">{{ $i+1 }}.</th>
                                                    <td>
                                                        <input type="hidden" name="part_name_id_{{ $i }}" value="{{ old('part_name_id_'.$i) }}">
                                                        <input type="text" class="form-control @error('part_name_'.$i) is-invalid @enderror"
                                                            id="part_name_{{ $i }}" name="part_name_{{ $i }}" maxlength="255"
                                                            value="@if (old('part_name_'.$i)){{ old('part_name_'.$i) }}@endif" />
                                                        @error('part_name_'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5"
                                                            value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif" style="text-align: right;"/>
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control @error('unit_'.$i) is-invalid @enderror"
                                                            id="unit_{{ $i }}" name="unit_{{ $i }}" maxlength="16"
                                                            value="@if (old('unit_'.$i)){{ old('unit_'.$i) }}@endif" />
                                                        @error('unit_'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" class="form-control" rows="3"
                                                            style="width: 100%;">@if (old('desc_part'.$i)){{ old('desc_part'.$i) }}@endif</textarea>
                                                        @error('desc_part'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                                </tr>
                                            {{-- @endif --}}
                                        @endfor
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($p_inquiryParts as $part)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">{{ $i+1 }}.</th>
                                                <td>
                                                    <input type="hidden" name="part_name_id_{{ $i }}" value="{{ $part->id }}">
                                                    <input type="text" class="form-control @error('part_name_'.$i) is-invalid @enderror"
                                                        id="part_name_{{ $i }}" name="part_name_{{ $i }}" maxlength="255"
                                                        value="@if (old('part_name_'.$i)){{ old('part_name_'.$i) }}@else{{ $part->part_name }}@endif" />
                                                    @error('part_name_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                        id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5"
                                                        value="@if (old('qty'.$i)){{ old('qty'.$i) }}@else{{ $part->qty }}@endif" style="text-align: right;"/>
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('unit_'.$i) is-invalid @enderror"
                                                        id="unit_{{ $i }}" name="unit_{{ $i }}" maxlength="16"
                                                        value="@if (old('unit_'.$i)){{ old('unit_'.$i) }}@else{{ $part->unit }}@endif" />
                                                    @error('unit_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" class="form-control" rows="3"
                                                        style="width: 100%;">@if (old('desc_part'.$i)){{ old('desc_part'.$i) }}@else{{ $part->description }}@endif</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($p_inquiries->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-light px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if (($p_inquiries->created_by==Auth::user()->id || $userLogin->is_director=='Y') && $p_inquiries->active=='Y')
                                        <input type="hidden" name="purchaseInquiryIds" id="purchaseInquiryIds">
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                    @endif
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Cancel">
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
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#header_txt,#footer_txt',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
});
</script>
<script>
    $(document).ready(function() {
        @if (($p_inquiries->created_by==Auth::user()->id || $userLogin->is_director=='Y') && $p_inquiries->active=='Y')
            $("#del-btn").click(function() {
                let msg = 'The following Purchase Inquiry Numbers will be canceled.\n{{ $p_inquiries->purchase_inquiry_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#purchaseInquiryIds").val('{{ $p_inquiries->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_purchase_inquiry') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

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
            
            // history.back();
            location.href = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow)+1);
            let vHtml = '<tr id="row'+totalRow+'">'+
                '<th scope="row" style="text-align:right;">'+rowNo+'.</th>'+
                '<td>'+
                '<input type="hidden" name="part_name_id_'+totalRow+'" value="0">'+
                '<input type="text" class="form-control" id="part_name_'+totalRow+'" name="part_name_'+totalRow+'" maxlength="255" />'+
                '</td>'+
                '<td><input type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" style="text-align: right;"/></td>'+
                '<td><input type="text" class="form-control" id="unit_'+totalRow+'" name="unit_'+totalRow+'" maxlength="16" /></td>'+
                '<td><textarea name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" class="form-control" rows="3" style="width: 100%;"></textarea></td>'+
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });

        $('#supplier_id').change(function() {
            $("#supplier_pic").empty();
            $("#supplier_pic").append(
                `<option value="#">Choose...</option>`
            );

            dispSupplierPic('supplier_id', '#supplier_id option:selected', '{{ url("disp_supplier_pic") }}', '#supplier_pic');
        });
    });
</script>
@endsection
