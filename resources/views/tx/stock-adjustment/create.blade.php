@extends('layouts.app')

@section('style')
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
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="branch_id" class="col-sm-3 col-form-label">Branch*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                        id="branch_id" name="branch_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('branch_id')?old('branch_id'):0);
                                                        @endphp
                                                        @foreach ($branch as $b)
                                                            <option @if ($p_Id==$b->id){{ 'selected' }}@endif value="{{ $b->id }}">{{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('branch_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" maxlength="512" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@endif</textarea>
                                                    @error('remark')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
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
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 30%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Part Type</th>
                                        <th scope="col" style="width: 10%;">Adj</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">SO</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Notes</th>
                                        <th scope="col" style="width: 2%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $lastTotalAmount = 0;
                                        $partHtml = '';
                                    @endphp
                                    @if (old('totalRow'))
                                        @for ($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if (old('part_id'.$lastIdx))
                                                @php
                                                    $query = \App\Models\Tx_qty_part::leftJoin('mst_parts AS msp','tx_qty_parts.part_id','=','msp.id')
                                                    ->leftJoin('mst_globals AS mg_unit','msp.quantity_type_id','=','mg_unit.id')
                                                    ->leftJoin('mst_globals AS mg_part_type','msp.part_type_id','=','mg_part_type.id')
                                                    ->select(
                                                        'msp.part_number',
                                                        'msp.part_name',
                                                        'msp.avg_cost',
                                                        'tx_qty_parts.qty AS OH_qty',
                                                        'mg_unit.string_val AS unit_name',
                                                        'mg_part_type.string_val AS part_type_name',
                                                        )
                                                    ->addSelect(['SO_qty' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0)')
                                                        ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                                                        ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                                        ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                                        ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                        ->where('tx_sales_order_parts.active','=','Y')
                                                        ->where('txso.active','=','Y')
                                                        ->whereIn('tx_sales_order_parts.id', function (\Illuminate\Database\Query\Builder $query) {
                                                            $query->select('sales_order_part_id')
                                                            ->from('tx_delivery_order_parts')
                                                            ->where('active','=','Y');
                                                        })
                                                    ])
                                                    ->where([
                                                        'tx_qty_parts.part_id' => old('part_id'.$lastIdx),
                                                        'tx_qty_parts.branch_id' => old('branch_id'),
                                                    ])
                                                    ->first();

                                                    $qty_adj_ = old('qty_adj_'.$lastIdx);
                                                    if($qty_adj_<0){
                                                        $qty_adj_ = $qty_adj_ * -1;
                                                    }
                                                @endphp
                                                <tr id="row{{ $lastIdx }}">
                                                    <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $lastIdx+1 }}.</label></th>
                                                    <td>
                                                        <select class="form-select partsAjax" id="part_id{{ $lastIdx }}" name="part_id{{ $lastIdx }}"
                                                            onchange="dispPartInfo(this.value,{{ $lastIdx }});">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partList = \App\Models\Mst_part::where([
                                                                    'id' => old('part_id'.$lastIdx),
                                                                ])
                                                                ->get();
                                                            @endphp
                                                            @foreach($partList as $p)
                                                                @php
                                                                    $partNumber = $p->part_number;
                                                                    if(strlen($partNumber)<11){
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                    }else{
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                    }
                                                                    $selected = '';
                                                                    if($p->id==old('part_id'.$lastIdx)){
                                                                        $selected = 'selected=""';
                                                                    }
                                                                    $partHtml .= '<option '.$selected.' value="'.$p->id.'">'.$partNumber.' : '.$p->part_name.'</option>';
                                                                @endphp
                                                            @endforeach
                                                            {!! $partHtml !!}
                                                        </select>
                                                    </td>
                                                    <td><label id="part_type_{{ $lastIdx }}" for="" class="col-form-label">{{ $query->part_type_name }}</label></td>
                                                    <td>
                                                        <input type="text" class="form-control @error('qty_adj_'.$lastIdx) is-invalid @enderror" style="text-align: right;"
                                                            id="qty_adj_{{ $lastIdx }}" name="qty_adj_{{ $lastIdx }}"
                                                            maxlength="15" value="@if (old('qty_adj_'.$lastIdx)){{ old('qty_adj_'.$lastIdx) }}@endif"
                                                            onchange="calcTotal(this.value,{{ $lastIdx }});" />
                                                        @error('qty_adj_'.$lastIdx)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td><label id="unit_{{ $lastIdx }}" for="" class="col-form-label">{{ $query->unit_name }}</label></td>
                                                    <td style="text-align:right;">
                                                        <label id="oh_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($query->OH_qty,0,'.',',') }}</label>
                                                        <input type="hidden" name="oh_ori_{{ $lastIdx }}" id="oh_ori_{{ $lastIdx }}" value="{{ $query->OH_qty }}">
                                                    </td>
                                                    <td style="text-align:right;">
                                                        <label id="so_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($query->SO_qty,0,'.',',') }}</label>
                                                        <input type="hidden" name="so_ori_{{ $lastIdx }}" id="so_ori_{{ $lastIdx }}" value="{{ $query->SO_qty }}">
                                                    </td>
                                                    <td style="text-align:right;">
                                                        <label id="avg_cost_{{ $lastIdx }}" for="" class="col-form-label">{{ number_format($query->avg_cost,0,'.',',') }}</label>
                                                        <input type="hidden" name="avg_cost_ori_{{ $lastIdx }}" id="avg_cost_ori_{{ $lastIdx }}" value="{{ $query->avg_cost }}">
                                                    </td>
                                                    <td style="text-align:right;">
                                                        <label id="total_{{ $lastIdx }}" for="" class="col-form-label">
                                                            {{ number_format(($qty_adj_*$query->avg_cost),0,'.',',') }}
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" name="notes_{{ $lastIdx }}" id="notes_{{ $lastIdx }}" rows="3"
                                                            style="width: 100%;">@if (old('notes_'.$lastIdx)){{ old('notes_'.$lastIdx) }}@endif</textarea>
                                                    </td>
                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck{{ $lastIdx }}" value="{{ $lastIdx }}"></td>
                                                    @php
                                                        $lastTotalAmount+= ($qty_adj_*$query->avg_cost);
                                                    @endphp
                                                </tr>
                                            @endif
                                        @endfor
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr id="rowTotal">
                                        <td colspan="7" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="btn-add-row" class="btn btn-primary px-5" value="Add Row">
                                    <input type="button" id="btn-del-row" class="btn btn-danger px-5" value="Remove Row">
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
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

    function addPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;"><label for="" class="col-form-label">'+rowNo+'.</label></th>'+
            '<td>'+
            '<select class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'" onchange="dispPartInfo(this.value,'+totalRow+');">'+
            '<option value="#">Choose...</option>'+
            '</select>'+
            '</td>'+
            '<td><label id="part_type_'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td><input type="text" class="form-control" style="text-align: right;" id="qty_adj_'+totalRow+'" name="qty_adj_'+totalRow+'" '+
            'maxlength="15" onchange="calcTotal(this.value,'+totalRow+');" /></td>'+
            '<td><label id="unit_'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td style="text-align:right;">'+
            '<label id="oh_'+totalRow+'" for="" class="col-form-label"></label>'+
            '<input type="hidden" name="oh_ori_'+totalRow+'" id="oh_ori_'+totalRow+'">'+
            '</td>'+
            '<td style="text-align:right;">'+
            '<label id="so_'+totalRow+'" for="" class="col-form-label"></label>'+
            '<input type="hidden" name="so_ori_'+totalRow+'" id="so_ori_'+totalRow+'">'+
            '</td>'+
            '<td style="text-align:right;">'+
            '<label id="avg_cost_'+totalRow+'" for="" class="col-form-label"></label>'+
            '<input type="hidden" name="avg_cost_ori_'+totalRow+'" id="avg_cost_ori_'+totalRow+'">'+
            '</td>'+
            '<td style="text-align:right;"><label id="total_'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td><textarea class="form-control" name="notes_'+totalRow+'" id="notes_'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);

        setPartsToDropdown();
    }

    function dispPartInfo(part_id,idx){
        $("#part_type_"+idx).text('');
        $("#unit_"+idx).text('');
        $("#oh_"+idx).text('');
        $("#oh_ori_"+idx).val(0);
        $("#avg_cost_"+idx).text('');
        $("#avg_cost_ori_"+idx).val(0);
        $("#so_"+idx).text('');
        $("#so_ori_"+idx).val(0);

        if($("#branch_id").val()==='#'){
            alert('Please select a valid branch');
            $("#new-row").empty();
            $("#totalRow").val(0);
            return false;
        }
        var fd = new FormData();
        fd.append('part_id', part_id);
        fd.append('branch_id', $("#branch_id").val());
        $.ajax({
            url: "{{ url('/disp_part_info_for_stockadj') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                $("#part_type_"+idx).text(o.part_type_name);
                $("#unit_"+idx).text(o.unit_name);
                $("#oh_"+idx).text(parseFloat(o.OH_qty).numberFormat(0,'.',','));
                $("#oh_ori_"+idx).val(o.OH_qty);
                $("#avg_cost_"+idx).text(parseFloat(o.avg_cost).numberFormat(0,'.',','));
                $("#avg_cost_ori_"+idx).val(o.avg_cost);
                $("#so_"+idx).text(parseFloat(o.SO_qty).numberFormat(0,'.',','));
                $("#so_ori_"+idx).val(o.SO_qty);
            },
        });

        $("#qty_adj_"+idx).val('');
        $("#total_"+idx).text('');
        calcGrandTotal();
    }

    function calcTotal(val,idx){
        if(!isNaN(val)){
            let newVal = val;
            if(val<0){newVal = val*-1;}
            if((parseInt(val)+parseInt($("#oh_ori_"+idx).val()))>=0){
                $("#total_"+idx).text(parseFloat($("#avg_cost_ori_"+idx).val()*newVal).numberFormat(0,'.',','));
            }else{
                $("#qty_adj_"+i).val('');
                $("#total_"+idx).text('');
            }
        }else{
            $("#total_"+idx).text('');
        }

        calcGrandTotal();
    }

    function calcGrandTotal(){
        let total = 0;
        for (i = 0; i < $("#totalRow").val(); i++) {
            if(!isNaN($("#qty_adj_"+i).val())){
                let newVal = $("#qty_adj_"+i).val();
                if($("#qty_adj_"+i).val()<0){newVal = $("#qty_adj_"+i).val()*-1;}
                if((parseInt($("#qty_adj_"+i).val())+parseInt($("#oh_ori_"+i).val()))>=0){
                    total = total+(newVal*$("#avg_cost_ori_"+i).val());
                }else{
                    $("#qty_adj_"+i).val('');
                }
            }
        }
        $("#lblTotalAmount").text("{{ $qCurrency->string_val }}"+parseFloat(total).numberFormat(0,'.',','));
    }

    $(document).ready(function() {
        $("#branch_id").change(function() {
            $("#new-row").empty();
            $("#lblTotalAmount").text('');
        });
        $("#btn-add-row").click(function() {
            addPart();
        });
        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            calcGrandTotal();
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
            
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
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
