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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$quotations->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if(session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Quotation No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $quotations->quotation_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Quotation Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($quotations->quotation_date), 'd/m/Y') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                    <div class="col-sm-9">
                                        <select
                                            class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                            id="supplier_id" name="supplier_id">
                                            <option value="#">Choose...</option>
                                            @php
                                            $supplierId = (old('supplier_id')?old('supplier_id'):$quotations->supplier_id);
                                            @endphp
                                            @foreach ($suppliers as $p)
                                            <option @if($supplierId==$p->id) {{ 'selected' }} @endif
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
                                        {!!
                                        (!is_null($supplierPics[0]->supplier_type)?$supplierPics[0]->entity_type->title_ind.' ':'').$supplierPics[0]->name.
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
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="supplier_pic" class="col-sm-3 col-form-label">Supplier PIC*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select @error('supplier_pic') is-invalid @enderror"
                                            id="supplier_pic" name="supplier_pic">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierPic = (old('supplier_pic')?old('supplier_pic'):$quotations->pic_idx);
                                            @endphp
                                            @foreach ($supplierPics as $p)
                                            <option @if($supplierPic==1) {{ 'selected' }} @endif value="1">
                                                {{ $p->pic1_name }}</option>
                                            @if(!is_null($p->pic2_name))
                                                <option @if($supplierPic==2) {{ 'selected' }} @endif value="2">
                                                    {{ $p->pic2_name }}
                                                </option>
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
                                            style="width:100%;">@if(old('header_txt')){{ old('header_txt') }}@else{{ $quotations->header }}@endif</textarea>
                                        @error('header_txt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="footer_txt" class="col-sm-3 col-form-label">Footer*</label>
                                    <div class="col-sm-9">
                                        <textarea name="footer_txt" id="footer_txt" rows="3" class="form-control @error('footer_txt') is-invalid @enderror"
                                            style="width:100%;">@if(old('footer_txt')){{ old('footer_txt') }}@else{{ $quotations->footer }}@endif</textarea>
                                        @error('footer_txt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="remark_txt" class="col-sm-3 col-form-label">Remark</label>
                                    <div class="col-sm-9">
                                        <textarea name="remark_txt" id="remark_txt" rows="3" class="form-control @error('remark_txt') is-invalid @enderror"
                                            style="width:100%;">@if(old('remark_txt')){{ old('remark_txt') }}@else{{ $quotations->remark }}@endif</textarea>
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
                                        <th scope="col" style="width: 2%;text-align:center;">#</th>
                                        <th scope="col" style="width: 35%;">Part</th>
                                        <th scope="col" style="width: 11%;">Qty</th>
                                        <th scope="col" style="width: 30%;">Description</th>
                                        <th scope="col" style="width: 10%;">Final Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 2%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if(old('totalRow'))
                                        @php
                                            $iRow = 1;
                                        @endphp
                                        @for ($i = 0; $i < $totRow; $i++)
                                            @if(old('part_id'.$i))
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" id="quotation_row_number{{ $i }}" style="text-align:right;">{{ $iRow }}.</th>
                                                    @php
                                                        $partNo = \App\Models\Mst_part::where([
                                                            'id' => old('part_id'.$i),
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    <td>
                                                        <input type="hidden" name="quotation_part_id_{{ $i }}" id="quotation_part_id_{{ $i }}" value="0">
                                                        <select onchange="dispPartRef(this.value, {{ $i }});"
                                                            class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                            id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partId = old('part_id'.$i);
                                                                $partList = \App\Models\Mst_part::where([
                                                                    'id' => $partId,
                                                                ])
                                                                ->get();
                                                            @endphp
                                                            @foreach ($partList as $pr)
                                                                @php
                                                                    $partNumber = $pr->part_number;
                                                                    if(strlen($partNumber)<11){
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                    }else{
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                    }
                                                                @endphp
                                                                <option @if($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('part_id'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror" id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5"
                                                            value="{{ old('qty'.$i) }}" style="text-align: right;"/>
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control @error('desc_part'.$i) is-invalid @enderror" name="desc_part{{ $i }}" id="desc_part{{ $i }}"
                                                            rows="3" style="width: 100%;">{{ old('desc_part'.$i) }}</textarea>
                                                        @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;"><label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ number_format($partNo->final_cost,0,'.',',') }}</label></td>
                                                    <td style="text-align: right;"><label id="avg-cost-{{ $i }}" for="" class="col-form-label">{{ number_format($partNo->avg_cost,0,'.',',') }}</label></td>
                                                    <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                                </tr>
                                                @php
                                                    $iRow++;
                                                @endphp
                                            @endif
                                        @endfor

                                    @else

                                        @php
                                            $i=0;
                                        @endphp
                                        @foreach($quotationParts AS $mp)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" id="quotation_row_number{{ $i }}" style="text-align:right;">{{ $i + 1 }}.</th>
                                                <td>
                                                    <input type="hidden" name="quotation_part_id_{{ $i }}" id="quotation_part_id_{{ $i }}" value="{{ $mp->id }}">
                                                    <select onchange="dispPartRef(this.value, {{ $i }});" class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                        id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partId = $mp->part_id;
                                                            $partList = \App\Models\Mst_part::where([
                                                                'id' => $partId,
                                                            ])
                                                            ->get();
                                                        @endphp
                                                        @foreach ($partList as $pr)
                                                            @php
                                                                $partNumber = $pr->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            @endphp
                                                            <option @if($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('part_id'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror" id="qty{{ $i }}" name="qty{{ $i }}"
                                                        maxlength="5" value="{{ $mp->qty }}" style="text-align: right;" />
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <textarea class="form-control @error('desc_part'.$i) is-invalid @enderror" name="desc_part{{ $i }}" id="desc_part{{ $i }}"
                                                        rows="3" style="width: 100%;">{{ $mp->description }}</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;"><label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ number_format($mp->part->final_cost,0,'.',',') }}</label></td>
                                                <td style="text-align: right;"><label id="avg-cost-{{ $i }}" for="" class="col-form-label">{{ number_format($mp->part->avg_cost,0,'.',',') }}</label></td>
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
                                    @if($quotations->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if(($quotations->created_by==Auth::user()->id || Auth::user()->id=1) && $quotations->active=='Y' && is_null($quotations->purchase_order))
                                        <input type="hidden" name="quotationId" id="quotationId">
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
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#mytextarea,#header_txt,#footer_txt',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
    // plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    // toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | '+
    //     'numlist bullist indent outdent | emoticons charmap | removeformat',
});
</script>
<script>
    function dispPartRef(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        $.ajax({
            url: "{{ url('/disp_quotation_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                if(o.length>0){
                    $('#final-cost-'+idx).text(parseFloat(o[0].final_cost).numberFormat(0,'.',','));
                    $('#avg-cost-'+idx).text(parseFloat(o[0].avg_cost).numberFormat(0,'.',','));
                }else{
                    $('#final-cost-'+idx).text(0);
                    $('#avg-cost-'+idx).text(0);
                }
            },
        });
    }

    function setPartsToDropdown(){
        $('.partsAjax').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
            placeholder: {
                id: "#",
                placeholder: "Choose..."
            },
            language: {
                inputTooShort: function (args) {
                    return "4 or more characters.";
                },
                noResults: function () {
                    return "Not Found.";
                },
                searching: function () {
                    return "Searching...";
                }
            },
            minimumInputLength: 4,
            ajax: {
                url: function (params) {
                    return '{{ url('/parts-json/?pnm=') }}'+params.term;
                },
                processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.part_name,
                            id: item.id
                        }
                    })
                };
            }}
        });
    }

    $(document).ready(function() {
        $(function () {
            Number.prototype.numberXFormat = function (decimals, dec_point, thousands_sep) {
                dec_point = typeof dec_point !== 'undefined' ? dec_point : '.';
                thousands_sep = typeof thousands_sep !== 'undefined' ? thousands_sep : ',';

                var parts = this.toFixed(decimals).split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);

                return parts.join(dec_point);
            }
        });
        @if(!old('supplier_id') && !isset($supplierPics[0]))
            $("#supplier_data").hide();
        @endif

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();

        $(function () {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
                $('#quotation_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
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
            if(!confirm("The data will be saved to the database. If it was previously in DRAFT status, it will be saved with CREATED status. Make sure the data entered is correct.\nContinue?")){
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
        @if($quotations->created_by==Auth::user()->id && $quotations->active=='Y' && is_null($quotations->purchase_order))
            $("#del-btn").click(function() {
                let msg = 'The following Quotation Numbers will be canceled.\n{{ $quotations->quotation_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#quotationId").val('{{ $quotations->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_quotation') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
                '<tr id="row' + totalRow + '">' +
                '<th scope="row" id="quotation_row_number'+totalRow+'" style="text-align:right;">' + rowNo + '.</th>' +
                '<td>'+
                    '<select onchange="dispPartRef(this.value, '+totalRow+');" class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                '<td><input type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" style="text-align: right;" value="" /></td>'+
                '<td><textarea class="form-control" name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>' +
                '<td style="text-align: right;"><label id="final-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>' +
                '<td style="text-align: right;"><label id="avg-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>' +
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#quotation_row_number"+i).text()){
                    $("#quotation_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            setPartsToDropdown();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#quotation_row_number"+i).text()){
                    $("#quotation_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
        setPartsToDropdown();

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
