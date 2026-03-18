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
                            <div class="row">
                                <div class="border p-4 rounded">
                                    <div class="col-xl-6">
                                        <div class="row mb-3">
                                            <label for="branch_from_id" class="col-sm-3 col-form-label">From*</label>
                                            <div class="col-sm-9">
                                                <select class="form-select single-select @error('branch_from_id') is-invalid @enderror"
                                                    id="branch_from_id" name="branch_from_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $branch_from_id = (old('branch_from_id')?old('branch_from_id'):$users->branch_id);
                                                    @endphp
                                                    @foreach ($qBranchFrom as $p)
                                                        <option @if ($branch_from_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_from_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="branch_to_id" class="col-sm-3 col-form-label">To*</label>
                                            <div class="col-sm-9">
                                                <select class="form-select single-select @error('branch_to_id') is-invalid @enderror"
                                                    id="branch_to_id" name="branch_to_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $branch_to_id = (old('branch_to_id')?old('branch_to_id'):0);
                                                    @endphp
                                                    @foreach ($qBranchTo as $p)
                                                        <option @if ($branch_to_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('branch_to_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="remark_txt" class="col-sm-3 col-form-label">Remark</label>
                                            <div class="col-sm-9">
                                                <textarea name="remark_txt" id="remark_txt" rows="3" class="form-control @error('remark_txt') is-invalid @enderror"
                                                    style="width:100%;">@if (old('remark_txt')){{ old('remark_txt') }}@endif</textarea>
                                                @error('remark_txt')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
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
                                        <th scope="col" style="width: 55%;">Part</th>
                                        {{-- <th scope="col" style="width: 15%;">Part Name</th> --}}
                                        <th scope="col" style="width: 10%;">Part Type</th>
                                        <th scope="col" style="width: 12%;">Qty</th>
                                        <th scope="col" style="width: 12%;">Unit</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++)
                                        @if (old('part_no_'.$i))
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;"><label for="" id="" class="col-form-label">{{ $i + 1 }}.</label></th>
                                                @php
                                                    $partNo = \App\Models\Mst_part::where([
                                                        'id' => old('part_no_'.$i),
                                                    ])
                                                    ->first();
                                                @endphp
                                                <td>
                                                    <select onchange="dispPartRef_No(this.value, {{ $i }});" class="form-select single-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
                                                        id="part-no-{{ $i }}" name="part_no_{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partNoId = old('part_no_'.$i)?old('part_no_'.$i):0;
                                                            $partList = \App\Models\Mst_part::where([
                                                                'id' => $partNoId,
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
                                                            <option @if ($partNoId==$pr->id){{ 'selected' }}@endif
                                                                value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('part_no_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                {{-- <td>
                                                    <label for="" id="part-name-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->part_name:'') }}</label>
                                                </td> --}}
                                                <td>
                                                    <label for="" id="part-type-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->part_type->string_val:'') }}</label>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror" id="qty{{ $i }}" name="qty{{ $i }}"
                                                        maxlength="5" value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif" style="text-align: right;" />
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <label for="" id="part-unit-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->quantity_type->string_val:'') }}</label>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                </td>
                                            </tr>
                                        @endif
                                    @endfor
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
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
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
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function dispPartRef_No(part_id, idx){
        let totalRow = $("#totalRow").val();
        let sameId = false;
        for(let iRow=0;iRow<totalRow;iRow++){
            if(iRow!=idx && part_id===$("#part-no-"+iRow).val()){
                alert('Choose another part number.');
                sameId = true;
                break;
            }
        }

        if(!sameId){
            var fd = new FormData();
            fd.append('part_id', part_id);
            fd.append('branch_id', $("#branch_from_id").val());
            $.ajax({
                url: "{{ url('/disp_stocktransfer_part_ref_info') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].parts;
                    if(typeof o[0]!=='undefined'){
                        $("#part-type-"+idx).text(o[0].part_type_name).change();
                        $("#part-unit-"+idx).text(o[0].part_unit_name).change();
                    }
                },
            });
        }else{
            $("#part-no-"+idx).val('#').change();
            $("#part-name-"+idx).text('').change();
            $("#part-type-"+idx).text('').change();
            $("#part-unit-"+idx).text('').change();
        }
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
        $("#branch_from_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);
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
            
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
                '<tr id="row' + totalRow + '">' +
                '<th scope="row" style="text-align:right;"><label for="" id="" class="col-form-label">' + rowNo + '.</label></th>' +
                '<td>'+
                    '<select onchange="dispPartRef_No(this.value, '+totalRow+');" class="form-select partsAjax" id="part-no-'+totalRow+'" name="part_no_'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                '<td><label for="" id="part-type-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td><input type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" style="text-align: right;" value="0" /></td>'+
                '<td><label for="" id="part-unit-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            setPartsToDropdown();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
        setPartsToDropdown();
    });
</script>
@endsection
