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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$stockAsm->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                            <div class="row">
                                <div class="border p-4 rounded">
                                    <div class="col-xl-6">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">SA No</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $stockAsm->stock_assembly_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Date</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($stockAsm->stock_assembly_date), 'd/m/Y') }}</label>
                                        </div>
                                        @if ($userLogin->is_director=='Y')
                                            <div class="row mb-3">
                                                <label for="branch_id" class="col-sm-3 col-form-label">Branch*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $branch_id = (old('branch_id')?old('branch_id'):$stockAsm->branch_id);
                                                        @endphp
                                                        @foreach ($branches as $branch)
                                                            <option @if ($branch_id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('branch_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @else
                                            <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">
                                        @endif
                                        <div class="row mb-3">
                                            <label for="part_id" class="col-sm-3 col-form-label">To be Part No*</label>
                                            <div class="col-sm-9">
                                                <select class="form-select partsAjax @error('part_id') is-invalid @enderror"
                                                    id="part_id" name="part_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $part_id = (old('part_id')?old('part_id'):$stockAsm->part_id);
                                                        $partList = \App\Models\Mst_part::where([
                                                            'id' => $part_id,
                                                        ])
                                                        ->get();
                                                    @endphp
                                                    @foreach ($partList as $p)
                                                        @php
                                                            $partNumber = $p->part_number;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <option @if ($part_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $partNumber.' : '.$p->part_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('part_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Part Name</label>
                                            <label for="" id="part_name" class="col-sm-9 col-form-label">{{ $stockAsm->part->part_name }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Qty*</label>
                                            <div class="col-sm-3">
                                                <input type="text" name="to_be_part_qty" id="to_be_part_qty" class="form-control @error('to_be_part_qty') is-invalid @enderror"
                                                    value="@if (old('to_be_part_qty')){{ old('to_be_part_qty') }}@else{{ $stockAsm->qty }}@endif" style="text-align: right;">
                                                @error('to_be_part_qty')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <label for="" class="col-sm-3 col-form-label" style="text-align:right;">Total Cost ({{ $qCurrency->string_val }})</label>
                                            <label for="" id="final-cost" class="col-sm-3 col-form-label" style="text-align:right;">{{ number_format($stockAsm->final_cost,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="remark_txt" class="col-sm-3 col-form-label">Remark</label>
                                            <div class="col-sm-9">
                                                <textarea name="remark_txt" id="remark_txt" rows="3" class="form-control @error('remark_txt') is-invalid @enderror"
                                                    style="width:100%;">@if (old('remark_txt')){{ old('remark_txt') }}@else{{ $stockAsm->remark }}@endif</textarea>
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
                            <input type="hidden" id="totalRow" name="totalRow" class="@error('totalRow') is-invalid @enderror" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 50%;">Part</th>
                                        {{-- <th scope="col" style="width: 15%;">Part Name</th> --}}
                                        <th scope="col" style="width: 7%;">Qty</th>
                                        <th scope="col" style="width: 7%;">Unit</th>
                                        <th scope="col" style="width: 15%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Total Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $total_avg_cost = 0;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for ($i = 0; $i < $totRow; $i++)
                                            @if (old('part_no_'.$i))
                                                <tr id="row{{ $i }}">
                                                    @php
                                                        $partNo = \App\Models\Mst_part::where([
                                                            'id' => old('part_no_'.$i),
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    @if($partNo)
                                                        @php
                                                            $total_avg_cost += $partNo->avg_cost*old('qty'.$i);
                                                        @endphp
                                                        <th scope="row" style="text-align:right;">
                                                            <label for="" id="row-no{{ $i }}" class="col-form-label">{{ $i + 1 }}.</label>
                                                            <input type="hidden" name="asmPartId{{ $i }}" id="asmPartId{{ $i }}" value="{{ old('asmPartId') }}">
                                                        </th>
                                                        <td>
                                                            <select onchange="dispPartRef_No(this.value, {{ $i }}, {{ $branch_id }});"
                                                                class="form-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
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
                                                            <input onchange="totalAVGcost();" type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                                id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5" value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif"
                                                                style="text-align: right;" />
                                                            @error('qty'.$i)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <label for="" id="part-unit-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->quantity_type->string_val:'') }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" id="avg-cost-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?number_format($partNo->avg_cost,0,'.',','):0) }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" id="total-avg-{{ $i }}"
                                                                class="col-form-label">{{ (!is_null($partNo)?number_format($partNo->avg_cost*old('qty'.$i),0,'.',','):0) }}</label>
                                                        </td>
                                                        <td style="text-align: center;">
                                                            <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endfor
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($stockAsmPart as $asmPart)
                                            <tr id="row{{ $i }}">
                                                @php
                                                    $partNo = \App\Models\Mst_part::where([
                                                        'id' => $asmPart->part_id,
                                                    ])
                                                    ->first();
                                                @endphp
                                                @if ($partNo)
                                                    @php
                                                        $total_avg_cost += $partNo->avg_cost*$asmPart->qty;
                                                    @endphp
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="row-no{{ $i }}" class="col-form-label">{{ $i + 1 }}.</label>
                                                        <input type="hidden" name="asmPartId{{ $i }}" id="asmPartId{{ $i }}" value="{{ $asmPart->id }}">
                                                    </th>
                                                    <td>
                                                        <select onchange="dispPartRef_No(this.value, {{ $i }}, {{ $branch_id }});"
                                                            class="form-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
                                                            id="part-no-{{ $i }}" name="part_no_{{ $i }}">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partNoId = $asmPart->part_id;
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
                                                        <input onchange="totalAVGcost();" type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5" value="{{ $asmPart->qty }}" style="text-align: right;" />
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <label for="" id="part-unit-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->quantity_type->string_val:'') }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="avg-cost-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?number_format($partNo->avg_cost,0,'.',','):0)  }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="total-avg-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?number_format($partNo->avg_cost*$asmPart->qty,0,'.',','):0)  }}</label>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                    </td>
                                                @endif
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" style="text-align: right;">
                                            <label for="" id="total-avg-cost-lbl" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" id="total-avg-cost-val" class="col-form-label">{{ number_format($total_avg_cost,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                            @error('totalRow')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($stockAsm->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if ($stockAsm->created_by==Auth::user()->id && $stockAsm->active=='Y' && strpos($stockAsm->stock_assembly_no,'Draft')>0)
                                        <input type="hidden" name="orderId" id="orderId">
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                    @endif
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
    function dispPartRef_No(part_id, idx, branch_id){
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
            @if ($userLogin->is_director=='Y')
            fd.append('branch_id', $("#branch_id").val());
            @else
            fd.append('branch_id', branch_id);
            @endif
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
                        $("#part-name-"+idx).text(o[0].part_name).change();
                        $("#part-unit-"+idx).text(o[0].part_unit_name).change();
                        $("#qty"+idx).val(0);
                        $("#avg-cost-"+idx).text(parseFloat(o[0].avg_cost).numberFormat(0,'.',','));
                        $("#total-avg-"+idx).text(parseFloat(o[0].avg_cost*0).numberFormat(0,'.',','));
                    }else{
                        alert('The desired part is not available in the warehouse.');
                    }
                },
            });
            totalAVGcost();
        }else{
            $("#part-no-"+idx).val('#').change();
            $("#part-name-"+idx).text('').change();
            $("#part-unit-"+idx).text('').change();
            $("#qty"+idx).val(0);
            $("#avg-cost-"+idx).text(0);
            $("#total-avg-"+idx).text(0);
        }
    }

    function totalAVGcost(){
        let totalRow = $("#totalRow").val();
        let total = 0;
        for(let iRow=0;iRow<totalRow;iRow++){
            if(!isNaN($("#qty"+iRow).val())){
                if(typeof $("#avg-cost-"+iRow).text()!=='undefined'){
                    avg_cost = parseFloat($("#avg-cost-"+iRow).text().replaceAll(",", ""))*$("#qty"+iRow).val();
                    // avg_cost = parseFloat($("#avg-cost-"+iRow).text().replaceAll(".", "").replaceAll(",", "."))*$("#qty"+iRow).val();
                    if(isNaN(avg_cost)){avg_cost=0;}
                    total += parseFloat(avg_cost);
                    $("#total-avg-"+iRow).text(parseFloat(avg_cost).numberFormat(0,'.',','));
                }
            }
        }
        if(!isNaN(total)){
            $("#total-avg-cost-val").text(parseFloat(total).numberFormat(0,'.',','));
        }else{
            $("#total-avg-cost-val").text(0);
        }
        $("#final-cost").text($("#total-avg-cost-val").text());
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
        $("#final-cost").text("{{ number_format($total_avg_cost,0,'.',',') }}");

        $("#part_id").change(function() {
            var fd = new FormData();
            fd.append('part_id', $("#part_id").val());
            $.ajax({
                url: "{{ url('/disp_part_info') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].part;
                    let totParts = o.length;
                    for (let i = 0; i < totParts; i++) {
                        $("#part_name").text(o[i].part_name);
                    }
                },
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
            
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
        @if ($stockAsm->created_by==Auth::user()->id && $stockAsm->active=='Y' && strpos($stockAsm->stock_assembly_no,'Draft')>0)
            $("#del-btn").click(function() {
                let msg = 'The following Stock Assembly Numbers will be canceled.\n{{ $stockAsm->stock_assembly_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#orderId").val('{{ $stockAsm->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_stockassembly') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $("#branch_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);
            totalAVGcost();
        });

        $("#btn-add-row").click(function() {
            @if ($userLogin->is_director=='Y')
            if($("#branch_id").val()==='#'){
                alert('Select a valid branch.');
                return false;
            }
            @endif

            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
                '<tr id="row' + totalRow + '">' +
                '<th scope="row" style="text-align:right;">'+
                    '<label for="" id="row-no'+totalRow+'" class="col-form-label">' + rowNo + '.</label>'+
                    '<input type="hidden" name="asmPartId'+totalRow+'" id="asmPartId'+totalRow+'">'+
                '</th>' +
                '<td>'+
                    '<select onchange="dispPartRef_No(this.value, '+totalRow+', {{ $branch_id }});" class="form-select partsAjax" id="part-no-'+totalRow+'" '+
                        'name="part_no_'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                // '<td><label for="" id="part-name-'+totalRow+'" class="col-form-label"></label></td>'+
                '<td>'+
                    '<input onchange="totalAVGcost();" type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" '+
                    'style="text-align: right;" value="0" />'+
                '</td>'+
                '<td><label for="" id="part-unit-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td style="text-align: right;"><label for="" id="avg-cost-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td style="text-align: right;"><label for="" id="total-avg-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            setPartsToDropdown();

            let idx = 0;
            for(iRow=0;iRow<$("#totalRow").val();iRow++){
                if($("#row-no"+iRow).length>0){
                    $("#row-no"+iRow).text((idx+1)+'.');
                    idx += 1;
                }
            }
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }
            totalAVGcost();

            let idx = 0;
            for(iRow=0;iRow<$("#totalRow").val();iRow++){
                if($("#row-no"+iRow).length>0){
                    $("#row-no"+iRow).text((idx+1)+'.');
                    idx += 1;
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
