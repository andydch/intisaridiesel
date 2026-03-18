@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet"
    href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
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
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierId = (old('supplier_id')?old('supplier_id'):0);
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
                                        @if(old('supplier_id'))
                                            @isset($supplierPics[0])
                                                {!!
                                                ($supplierPics[0]->entity_type?$supplierPics[0]->entity_type->title_ind.' ':'').$supplierPics[0]->name.
                                                '<br />Address: '.$supplierPics[0]->office_address.
                                                (($supplierPics[0]->subdistrict?$supplierPics[0]->subdistrict->sub_district_name:'')=='Other'?'':
                                                ', '.ucwords(strtolower(($supplierPics[0]->subdistrict?$supplierPics[0]->subdistrict->sub_district_name:'')))).
                                                (($supplierPics[0]->district?$supplierPics[0]->district->district_name:'')=='Other'?'':
                                                ', '.($supplierPics[0]->district?$supplierPics[0]->district->district_name:'')).
                                                (($supplierPics[0]->city?$supplierPics[0]->city->city_name:'')=='Other'?'':
                                                '<br />'.(($supplierPics[0]->city?$supplierPics[0]->city->city_type:'')=='Luar Negeri'?'':($supplierPics[0]->city?$supplierPics[0]->city->city_type:'')).' '.
                                                ($supplierPics[0]->city?$supplierPics[0]->city->city_name:'')).
                                                (($supplierPics[0]->province?$supplierPics[0]->province->province_name:'')=='Other'?'':
                                                ($supplierPics[0]->province?'<br />'.$supplierPics[0]->province->province_name:'')).
                                                ($supplierPics[0]->country?'<br />'.$supplierPics[0]->country->country_name:'').
                                                ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                                ' '.$supplierPics[0]->subdistrict->post_code)
                                                !!}
                                            @endisset
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="supplier_pic" class="col-sm-3 col-form-label">PIC*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('supplier_pic') is-invalid @enderror" id="supplier_pic" name="supplier_pic">
                                            <option value="#">Choose...</option>
                                            @php
                                                $supplierPic = (old('supplier_pic')?old('supplier_pic'):0);
                                            @endphp
                                            @foreach ($supplierPics as $p)
                                                <option @if ($supplierPic==1) {{ 'selected' }} @endif value="1">{{ $p->pic1_name }}</option>
                                                @if (!is_null($p->pic2_name))
                                                    <option @if ($supplierPic==2) {{ 'selected' }} @endif value="2">
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
                                @if ($userLogin->is_director=='Y')
                                    <input type="hidden" name="is_director" id="is_director" value="Y">
                                    <div class="row mb-3">
                                        <label for="branch_id" class="col-sm-3 col-form-label">Ship To*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="">Choose...</option>
                                                @php
                                                    $branch_id = old('branch_id')?old('branch_id'):0;
                                                @endphp
                                                @foreach ($branches as $p)
                                                    <option @if($branch_id==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="is_director" id="is_director" value="N">
                                    <input type="hidden" name="branch_id" id="branch_id" value="">
                                @endif
                                <div class="row mb-3">
                                    <label for="active" class="col-sm-3 col-form-label">VAT</label>
                                    <div class="col-sm-9">
                                        @php
                                            $vat = 'N';
                                        @endphp
                                        @if (old('vat'))
                                            @if (old('vat') == 'on')
                                                @php
                                                    $vat = 'Y';
                                                @endphp
                                            @else
                                                @php
                                                    $vat = 'N';
                                                @endphp
                                            @endif
                                        @endif
                                        <input class="form-check-input" type="checkbox" id="vat" name="vat"
                                            aria-label="VAT" @if ($vat=='Y'){{ 'checked' }}@endif>
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
                                <div class="row mb-3">
                                    <label for="total_price" class="col-sm-3 col-form-label">Total Price</label>
                                    <label for="" class="col-sm-1 col-form-label">{{ $qCurrency->string_val }}</label>
                                    <div class="col-sm-8">
                                        <input readonly type="text" name="total_price" id="total_price" class="form-control @error('total_price') is-invalid @enderror"
                                            style="width: 50%;text-align: right;" value="@if (old('total_price')){{ old('total_price') }}@endif">
                                        @error('total_price')
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
                                        <th scope="col" style="width: 28%;">Part</th>
                                        <th scope="col" style="width: 8%;">Qty</th>
                                        <th scope="col" style="width: 12%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Description</th>
                                        <th scope="col" style="width: 10%;">Final Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">OO</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++)
                                        @if (old('part_id'.$i))
                                            <tr id="row{{ $i }}">
                                                <th scope="row" id="memo_row_number{{ $i }}" style="text-align:right;">{{ $i+1 }}.</th>
                                                @php
                                                    $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                                                    ->select(
                                                        'mst_parts.*',
                                                        'tx_qty_parts.qty as total_qty'
                                                    )
                                                    ->addSelect(['purchase_memo_qty' => \App\Models\Tx_purchase_memo_part::selectRaw('IFNULL(SUM(qty),0)')    // total qty dari memo yg aktif
                                                        ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                                                        ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                                        ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
                                                        ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                        ->where('tx_purchase_memo_parts.active','=','Y')
                                                        ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                                        ->where('tx_memo.active','=','Y')
                                                    ])
                                                    ->addSelect(['purchase_order_qty' => \App\Models\Tx_purchase_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari po yg aktif
                                                        ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                                                        ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                                        ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
                                                        ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                        ->where('tx_purchase_order_parts.active','=','Y')
                                                        ->where('tx_order.approved_by','<>',null)
                                                        ->where('tx_order.active','=','Y')
                                                    ])
                                                    ->addSelect(['purchase_ro_qty' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
                                                        ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                        ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                        ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                                                        ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                        ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                                        ->where('tx_receipt_order_parts.active','=','Y')
                                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                        ->where('tx_ro.active','=','Y')
                                                    ])
                                                    ->addSelect(['purchase_ro_qty_no_partial' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO dg is_partial_received=N
                                                        ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                        ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                        ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                                                        ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                        ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                                        ->where('tx_receipt_order_parts.active','=','Y')
                                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                        ->where('tx_ro.active','=','Y')
                                                    ])
                                                    ->where([
                                                        'mst_parts.id' => old('part_id'.$i),
                                                        'tx_qty_parts.branch_id' => $userLogin->branch_id
                                                    ])
                                                    ->first();
                                                @endphp
                                                <td>
                                                    <select onchange="dispPartRef_PartNo(this.value, {{ $i }});" class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                        id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partId = old('part_id'.$i) ? old('part_id'.$i) : 0;
                                                            $partList = \App\Models\Mst_part::where([
                                                                'id' => old('part_id'.$i),
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
                                                            <option @if ($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '. $pr->part_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('part_id'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" onkeyup="totPrice({{ $i }});" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                        id="qty{{ $i }}" name="qty{{ $i }}" maxlength="5" value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif" />
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" onkeyup="formatPartPrice({{ $i }});"
                                                        class="form-control @error('price'.$i) is-invalid @enderror"
                                                        id="price{{ $i }}" name="price{{ $i }}" maxlength="25"
                                                        value="@if (old('price'.$i)){{ old('price'.$i) }}@endif" style="text-align: right;"/>
                                                    @error('price'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                @php
                                                    $price = old('price'.$i)?old('price'.$i):0;
                                                    $price = str_replace(",","",$price);
                                                @endphp
                                                <td style="text-align: right;">
                                                    <label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format(old('qty'.$i)*$price,0,'.',',') }}</label>
                                                </td>
                                                <td>
                                                    <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" class="form-control" rows="3"
                                                        style="width: 100%;">@if (old('desc_part'.$i)){{ old('desc_part'.$i) }}@endif</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_cost,0,'.',',')) }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format((!is_null($partNo)?$partNo->total_qty:0),0,'.',',') }}</label>
                                                </td>
                                                @php
                                                    $oo = 0;
                                                    if(!is_null($partNo)){
                                                        $oo = ($partNo->purchase_memo_qty+$partNo->purchase_order_qty)-($partNo->purchase_ro_qty+$partNo->purchase_ro_qty_no_partial);
                                                    }
                                                @endphp
                                                <td style="text-align: right;">
                                                    <label id="oo-{{ $i }}" for="" class="col-form-label">{{ number_format($oo,0,'.',',') }}</label>
                                                </td>
                                                <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
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
    function totPrice(idx){
        let qtyTemp = 0;
        let priceTemp = 0;
        if($.isNumeric($('#qty'+idx).val())){
            qtyTemp = $('#qty'+idx).val();
        }
        let priceOriginal = $('#price'+idx).val().replaceAll(",","");
        if($.isNumeric(priceOriginal)){
            priceTemp = priceOriginal;
        }
        $('#total-price-'+idx).text((qtyTemp*priceTemp).numberFormat(0,'.',','));

        totAllPrice();
    }
    function totAllPrice(){
        let totalPrice = 0;
        for(let iRow=0;iRow<$('#totalRow').val();iRow++){
            if (typeof $('#qty'+iRow).val() !== 'undefined') {
                totalPrice += ($('#qty'+iRow).val()*$('#price'+iRow).val().replaceAll(",",""));
            }
        }
        $('#total_price').val(totalPrice.numberFormat(0,'.',','));
    }
    function dispPartRef(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        $.ajax({
            url: "{{ url('/disp_memo_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                $('#final-cost-'+idx).text(parseFloat(o[0].final_cost).numberFormat(0,'.',','));
            },
        });
    }
    function dispPartRef_PartNo(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        $.ajax({
            url: "{{ url('/disp_memo_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                // $("#part_id"+idx).val(o[0].part_id).change();
                $('#final-cost-'+idx).text(parseFloat(o[0].final_cost).numberFormat(0,'.',','));
                $('#oh-'+idx).text(parseFloat(o[0].total_qty).numberFormat(0,'.',','));
                let oo = (parseInt(o[0].purchase_memo_qty)+parseInt(o[0].purchase_order_qty))-(parseInt(o[0].purchase_ro_qty)+parseInt(o[0].purchase_ro_qty_no_partial));
                $('#oo-'+idx).text(parseFloat(oo).numberFormat(0,'.',','));
            },
        });
    }
    function formatPartPrice(idx){
        let priceList = $("#price"+idx).val().replaceAll(',','');
        if(priceList===''){$("#price"+idx).val('');return false;}
        if(isNaN(priceList)){$("#price"+idx).val('');return false;}
        priceList = parseFloat(priceList).numberFormat(0,'.',',');    // without decimal
        $("#price"+idx).val(priceList);

        totPrice(idx);
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
        @if(!old('supplier_id') || !isset($supplierPics[0]))
            $("#supplier_data").hide();
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

        $('.datepicker').pickadate({
                selectMonths: true,
                selectYears: true
            }),
            $('.timepicker').pickatime();

        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#memo_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow)+1);
            let vHtml = '<tr id="row'+totalRow+'">'+
                '<th scope="row" id="memo_row_number'+totalRow+'" style="text-align:right;">'+rowNo+'.</th>'+
                '<td>'+
                    '<select onchange="dispPartRef_PartNo(this.value, '+totalRow+');" class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                '<td><input onchange="totPrice('+totalRow+');" type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="5" '+
                'style="text-align: right;" value="" /></td>'+
                '<td><input onkeyup="formatPartPrice('+totalRow+');" type="text" class="form-control" '+
                'id="price'+totalRow+'" name="price'+totalRow+'" maxlength="25" style="text-align: right;" value="" /></td>'+
                '<td style="text-align: right;"><label id="total-price-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td><textarea class="form-control" class="form-control" name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>'+
                '<td style="text-align: right;"><label id="final-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align: right;"><label id="oh-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align: right;"><label id="oo-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#memo_row_number"+i).text()){
                    $("#memo_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            setPartsToDropdown();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#memo_row_number"+i).text()){
                    $("#memo_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            totAllPrice();
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
