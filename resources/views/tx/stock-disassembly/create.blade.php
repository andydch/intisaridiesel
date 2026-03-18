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
                                        @if ($userLogin->is_director=='Y')
                                            <div class="row mb-3">
                                                <label for="branch_id" class="col-sm-3 col-form-label">Branch*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $branch_id = (old('branch_id')?old('branch_id'):0);
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
                                            <label for="part_id" class="col-sm-3 col-form-label">Part No*</label>
                                            <div class="col-sm-9">
                                                <select class="form-select partsAjax @error('part_id') is-invalid @enderror" id="part_id" name="part_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $part_id = (old('part_id')?old('part_id'):0);
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
                                            <label for="" id="part_name" class="col-sm-9 col-form-label">@if (old('part_id')){{ !is_null($oldParts)?$oldParts->part_name:'' }}@endif</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">AVG Cost ({{ $qCurrency->string_val }})</label>
                                            <label for="" id="avg-cost-part-to-be-disassembly"
                                                class="col-sm-9 col-form-label">@if (old('part_id')){{ !is_null($oldParts)?number_format($oldParts->avg_cost*old('disasm_part_qty'),0,'.',','):'' }}@endif</label>
                                            <input type="hidden" name="avg_cost_part_to_be_disassembly_val" id="avg_cost_part_to_be_disassembly_val"
                                                value="@if (old('avg_cost_part_to_be_disassembly_val')){{ old('avg_cost_part_to_be_disassembly_val') }}@else{{ 0 }}@endif">
                                            <input type="hidden" name="avg_cost_part_to_be_disassembly_val_tmp" id="avg_cost_part_to_be_disassembly_val_tmp"
                                                value="@if (old('avg_cost_part_to_be_disassembly_val_tmp')){{ old('avg_cost_part_to_be_disassembly_val_tmp') }}@else{{ 0 }}@endif">
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Qty*</label>
                                            <div class="col-sm-3">
                                                <input type="text" name="disasm_part_qty" id="disasm_part_qty" class="form-control @error('disasm_part_qty') is-invalid @enderror"
                                                    value="@if (old('disasm_part_qty')){{ old('disasm_part_qty') }}@endif" style="text-align: right;">
                                                @error('disasm_part_qty')
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
                            <input type="hidden" id="totalRow" name="totalRow" class="@error('totalRow') is-invalid @enderror" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 43%;">To Be Part No</th>
                                        {{-- <th scope="col" style="width: 15%;">Part Name</th> --}}
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 7%;">Unit</th>
                                        <th scope="col" style="width: 15%;">Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 2%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $total_cost = 0;
                                    @endphp
                                    @for ($i = 0; $i < $totRow; $i++)
                                        @if (old('part_no_'.$i))
                                            <tr id="row{{ $i }}">
                                                @php
                                                    $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                                                    ->select(
                                                        'mst_parts.*',
                                                        'txQty.qty'
                                                    )
                                                    ->where([
                                                        'mst_parts.id' => old('part_no_'.$i),
                                                        'txQty.branch_id' => $branch_id
                                                    ])
                                                    ->first();
                                                @endphp
                                                @if($partNo)
                                                    @php
                                                        $oldNewCost = str_replace(",","",old('new_cost'.$i));
                                                        // dd('tes '.old('new_cost'.$i));
                                                        $total_cost += $oldNewCost*old('qty'.$i);
                                                        $avg_cost = (($partNo->avg_cost*$partNo->qty)+($oldNewCost*old('qty'.$i)))/($partNo->qty+old('qty'.$i));
                                                    @endphp
                                                    <th scope="row" style="text-align:right;"><label for="" id="row-no{{ $i }}" class="col-form-label">{{ $i + 1 }}.</label></th>
                                                    <td>
                                                        <select onchange="dispPartRef_No(this.value, {{ $i }}, {{ $branch_id }});" class="form-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
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
                                                    <td>
                                                        <input onchange="multiplicationCost({{ $i }});" type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5" value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif"
                                                            style="text-align: right;" />
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <label for="" id="part-unit-{{ $i }}" class="col-form-label">{{ (!is_null($partNo)?$partNo->quantity_type->string_val:'') }}</label>
                                                    </td>
                                                    <td>
                                                        <input onkeyup="multiplicationCost({{ $i }});formatAmount($(this));" type="text"
                                                            class="form-control @error('new_cost'.$i) is-invalid @enderror" id="new_cost{{ $i }}" name="new_cost{{ $i }}"
                                                            maxlength="20" value="@if (old('new_cost'.$i)){{ old('new_cost'.$i) }}@else{{ 0 }}@endif" style="text-align: right;" />
                                                        @error('new_cost'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="total-cost-{{ $i }}" class="col-form-label">@if (old('part_no_'.$i)){{ number_format((old('qty'.$i)*$oldNewCost),0,'.',',') }}@endif</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label for="" id="avg-cost-{{ $i }}" class="col-form-label">@if (old('part_no_'.$i)){{ number_format($avg_cost,0,'.',',') }}@endif</label>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" style="text-align: right;">
                                            <label for="" id="total-avg-cost-lbl" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" id="total-cost-val" class="col-form-label">{{ number_format($total_cost,0,'.',',') }}</label>
                                            <input type="hidden" name="total_cost_val" id="total_cost_val" class="form-control @error('avg_cost_part_to_be_disassembly_val') is-invalid @enderror" value="{{ $total_cost }}">
                                            @error('avg_cost_part_to_be_disassembly_val')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>&nbsp;</td>
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
    function formatAmount(elm){
        let amount = elm.val().replaceAll(',','');
        if(amount===''){elm.val('');return false;}
        if(isNaN(amount)){elm.val('');return false;}
        amount = parseFloat(amount).numberFormat(0,'.',',');
        elm.val(amount);

        // set cursor position
        // console.log(elm.val().length);
        // if(elm.val().length>=3){
        //     elm.selectRange(elm.val().length-3); // set cursor position
        // }
    }

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
                        $("#avg-cost-"+idx).text(parseFloat(o[0].avg_cost).numberFormat(0,'.',','));
                    }else{
                        alert('The desired part is not available in the warehouse.');
                    }
                },
            });
            multiplicationCost(idx);
        }else{
            $("#part-no-"+idx).val('#').change();
            $("#part-name-"+idx).text('').change();
            $("#part-unit-"+idx).text('').change();
            $("#avg-cost-"+idx).text(0);
        }
    }

    function totalCost(){
        let totalRow = $("#totalRow").val();
        let total = 0;
        for(let iRow=0;iRow<totalRow;iRow++){
            let qty = $("#qty"+iRow).val();
            let new_cost = $("#new_cost"+iRow).val().replaceAll(",","");
            if(!isNaN(qty) && !isNaN(new_cost)){
                if(typeof $("#total-cost-"+iRow).text()!=='undefined'){
                    let total_cost = parseFloat($("#total-cost-"+iRow).text().replaceAll(",",""));
                    if(isNaN(total_cost)){total_cost=0;}
                    total += parseFloat(total_cost);
                    $("#total-avg-"+iRow).text(parseFloat(total_cost).numberFormat(0,'.',','));
                }
            }
        }

        if(!isNaN(total)){
            $("#total-cost-val").text(parseFloat(total).numberFormat(0,'.',','));
            $("#total_cost_val").val(total);
        }else{
            $("#total-cost-val").text(0);
            $("#total_cost_val").val(0);
        }
    }

    function multiplicationCost(idx){
        let newCost = $("#new_cost"+idx).val().replaceAll(',','');
        if(newCost===''){newCost = 0;}
        if(!isNaN($("#qty"+idx).val()) && !isNaN(newCost) && $("#part-no-"+idx).val()!=='#'){
            $("#total-cost-"+idx).text(($("#qty"+idx).val()*newCost).numberFormat(0,'.',','));
            totalCost();

            var fd = new FormData();
            fd.append('part_id', $("#part-no-"+idx).val());
            // fd.append('branch_id', {{ $branch_id }});
            @if ($userLogin->is_director=='Y')
            fd.append('branch_id', $("#branch_id").val());
            @else
            fd.append('branch_id', {{ $branch_id }});
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
                        let qtyIdx = $("#qty"+idx).val();
                        if(isNaN(qtyIdx) || qtyIdx==='' || qtyIdx===null){qtyIdx = 0;}
                        let avg_cost = ((parseFloat(o[0].avg_cost)*parseInt(o[0].total_qty))+(parseInt(qtyIdx)*parseFloat(newCost)))/(parseInt(o[0].total_qty)+parseInt(qtyIdx));
                        if((parseInt(o[0].total_qty)+parseInt(qtyIdx))===0){
                            $("#avg-cost-"+idx).text(0);
                        }else{
                            $("#avg-cost-"+idx).text(parseFloat(avg_cost).numberFormat(0,'.',','));
                        }
                    }else{
                        alert('The desired part is not available in the warehouse.');
                    }
                },
            });
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
        @if(old('totalRow'))
            $("#total-cost-val").text("{{ number_format($total_cost,0,'.',',') }}");
            $("#total_cost_val").val("{{ $total_cost }}");
        @endif

        $("#disasm_part_qty").change(function() {
            if (isNaN($("#disasm_part_qty").val())){
                $("#disasm_part_qty").val(0);
                return false;
            }

            let avg_cost_tot = $("#avg_cost_part_to_be_disassembly_val_tmp").val()*$("#disasm_part_qty").val();       
            $("#avg-cost-part-to-be-disassembly").text(parseFloat(avg_cost_tot).numberFormat(0,'.',','));
            $("#avg_cost_part_to_be_disassembly_val").val(parseFloat(avg_cost_tot).numberFormat(0,',',''));
        });

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
                        $("#avg-cost-part-to-be-disassembly").text(parseFloat(o[i].avg_cost).numberFormat(0,'.',','));
                        $("#avg_cost_part_to_be_disassembly_val").val(parseFloat(o[i].avg_cost).numberFormat(0,',',''));
                        $("#avg_cost_part_to_be_disassembly_val_tmp").val(parseFloat(o[i].avg_cost));

                        $("#disasm_part_qty").val(1);
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

        $("#branch_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);
            totalCost();
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
                '<th scope="row" style="text-align:right;"><label for="" id="row-no'+totalRow+'" class="col-form-label">' + rowNo + '.</label></th>' +
                '<td>'+
                    '<select onchange="dispPartRef_No(this.value, '+totalRow+', {{ $branch_id }});" class="form-select partsAjax" id="part-no-'+totalRow+'" '+
                        'name="part_no_'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                '<td>'+
                    '<input onchange="multiplicationCost('+totalRow+');" type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" '+
                    'style="text-align: right;" value="" />'+
                '</td>'+
                '<td><label for="" id="part-unit-'+totalRow+'" class="col-form-label"></label></td>' +
                '<td>'+
                    '<input onkeyup="multiplicationCost('+totalRow+');formatAmount($(this));" type="text" class="form-control" id="new_cost'+totalRow+'" '+
                    'name="new_cost'+totalRow+'" maxlength="22" '+
                    'style="text-align: right;" value="" />'+
                '</td>'+
                '<td style="text-align: right;"><label for="" id="total-cost-'+totalRow+'" class="col-form-label">0</label></td>' +
                '<td style="text-align: right;"><label for="" id="avg-cost-'+totalRow+'" class="col-form-label">0</label></td>' +
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
            totalCost();

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
