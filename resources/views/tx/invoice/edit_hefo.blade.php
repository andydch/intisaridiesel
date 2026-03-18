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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'-hefo/'.$qInv->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                            <div class="row mb-3">
                                <div class="col-xl-9">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">INV No</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->invoice_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->customer->customer_unique_code.' - '.$qInv->customer->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">FK No</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 97%;text-align:center;">FK No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-fk">
                                                            @php
                                                                $iRow=0;
                                                                $all_selected_FK=explode(",",$all_selected_FK_from_db);
                                                            @endphp
                                                            @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                                                @if ($all_selected_FK[$lastCounter]!='')
                                                                    @php
                                                                        $iRow+=1;
                                                                        $qFK = \App\Models\Tx_delivery_order::where('delivery_order_no','=',$all_selected_FK[$lastCounter])
                                                                        ->first();
                                                                    @endphp
                                                                    <tr id="rowFK{{ $lastCounter }}">
                                                                        <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $iRow }}.</label></td>
                                                                        <td>
                                                                            <label for="" name="fk_no{{ $lastCounter }}" id="fk_no{{ $lastCounter }}"
                                                                                class="col-form-label">{{ $all_selected_FK[$lastCounter] }}</label>
                                                                            <input type="hidden" name="fk_id{{ $lastCounter }}" id="fk_id{{ $lastCounter }}" value="{{ $lastCounter }}">
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Payment To</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->coa?$qInv->coa->coa_name:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Plan Date</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qInv->invoice_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qInv->branch)?$qInv->branch->name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="header" class="col-sm-3 col-form-label">Header</label>
                                                <div class="col-sm-9">
                                                    <textarea name="header" id="header" rows="3" class="form-control @error('header') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('header')){{ old('header') }}@else{{ $qInv->header }}@endif</textarea>
                                                    @error('header')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="footer" class="col-sm-3 col-form-label">Footer</label>
                                                <div class="col-sm-9">
                                                    <textarea name="footer" id="footer" rows="3" class="form-control @error('footer') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('footer')){{ old('footer') }}@else{{ $qInv->footer }}@endif</textarea>
                                                    @error('footer')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Paid for</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@else{{ $qInv->remark }}@endif</textarea>
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
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col">FK Date</th>
                                        <th scope="col">FK No</th>
                                        <th scope="col">FP No</th>
                                        <th scope="col">SO No</th>
                                        <th scope="col">DPP ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">PPN ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">Cust Doc No</th>
                                    </tr>
                                </thead>
                                <tbody id="new-rowfk-detail">
                                    @php
                                        $grandTotalVal=0;
                                        $totalValbeforeVAT=0;
                                        $totalValafterVAT=0;
                                    @endphp
                                    @if (old('all_selected_FK'))
                                        @php
                                            $iRow=0;
                                        @endphp
                                        @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                            @if ($all_selected_FK[$lastCounter]!='')
                                                @php
                                                    $vat=0;
                                                    $qFK = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                                                    ->select(
                                                        'tx_delivery_orders.*',
                                                        'tx_delivery_orders.id as faktur_id',
                                                        'tx_tax_invoices.fp_no',
                                                    )
                                                    ->where('tx_delivery_orders.delivery_order_no','=',$all_selected_FK[$lastCounter])
                                                    ->first();
                                                @endphp
                                                @if ($qFK)
                                                    @if ($qFK->is_vat=='Y')
                                                        @php
                                                            $vat=$qFK->total_after_vat-$qFK->total_before_vat;
                                                        @endphp
                                                    @endif
                                                    @php
                                                        // nota retur - begin
                                                        $retur_total_before_vat = 0;
                                                        $nota_retur = \App\Models\Tx_nota_retur::select(
                                                            'total_before_vat'
                                                        )
                                                        ->whereRaw('approved_by IS NOT null')
                                                        ->where([
                                                            'delivery_order_id'=>$qFK->faktur_id,
                                                            'active'=>'Y',
                                                        ])
                                                        ->first();
                                                        if ($nota_retur){
                                                            $retur_total_before_vat = $nota_retur->total_before_vat;
                                                        }
                                                        // nota retur - end

                                                        $all_cust_doc_no_arr=explode(",",$qFK->sales_order_no_all);
                                                        $all_cust_doc_no='';
                                                        $grandTotalVal+=($qFK->total_after_vat-$retur_total_before_vat);
                                                    @endphp
                                                    @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                        @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                            @php
                                                                $so = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_cust_doc_no_arr[$c_doc])
                                                                ->first();
                                                            @endphp
                                                            @if ($so)
                                                                @php
                                                                    $all_cust_doc_no.=','.$so->customer_doc_no;
                                                                @endphp
                                                            @endif
                                                        @endif
                                                    @endfor
                                                    <tr id="rowFKdetail{{ $iRow }}">
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ date_format(date_create($qFK->delivery_order_date), 'd/m/Y') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->delivery_order_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fp_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->fp_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qFK->sales_order_no_all,1,strlen($qFK->sales_order_no_all)) }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_before_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_before_vat-$retur_total_before_vat),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($vat,0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_after_vat-$retur_total_before_vat),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $totalValbeforeVAT+=($qFK->total_before_vat-$retur_total_before_vat);
                                                        $totalValafterVAT+=($qFK->total_after_vat-$retur_total_before_vat);
                                                    @endphp
                                                @endif
                                                @php
                                                    $iRow+=1;
                                                @endphp
                                            @endif
                                        @endfor
                                    @else
                                        @php
                                            $iRow=0;
                                        @endphp
                                        @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                            @if ($all_selected_FK[$lastCounter]!='')
                                                @php
                                                    $vat=0;
                                                    $qFK = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                                                    ->select(
                                                        'tx_delivery_orders.*',
                                                        'tx_delivery_orders.id as faktur_id',
                                                        'tx_tax_invoices.fp_no',
                                                    )
                                                    ->where('tx_delivery_orders.delivery_order_no','=',$all_selected_FK[$lastCounter])
                                                    ->first();
                                                @endphp
                                                @if ($qFK)
                                                    @if ($qFK->is_vat=='Y')
                                                        @php
                                                            $vat=$qFK->total_after_vat-$qFK->total_before_vat;
                                                        @endphp
                                                    @endif
                                                    @php
                                                        // nota retur - begin
                                                        $retur_total_before_vat = 0;
                                                        $nota_retur = \App\Models\Tx_nota_retur::select(
                                                            'total_before_vat'
                                                        )
                                                        ->whereRaw('approved_by IS NOT null')
                                                        ->where([
                                                            'delivery_order_id'=>$qFK->faktur_id,
                                                            'active'=>'Y',
                                                        ])
                                                        ->first();
                                                        if ($nota_retur){
                                                            $retur_total_before_vat = $nota_retur->total_before_vat;
                                                        }
                                                        // nota retur - end

                                                        $all_cust_doc_no_arr=explode(",",$qFK->sales_order_no_all);
                                                        $all_cust_doc_no='';
                                                        $grandTotalVal+=($qFK->total_after_vat-$retur_total_before_vat);
                                                    @endphp
                                                    @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                        @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                            @php
                                                                $so = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_cust_doc_no_arr[$c_doc])
                                                                ->first();
                                                            @endphp
                                                            @if ($so)
                                                                @php
                                                                    $all_cust_doc_no.=','.$so->customer_doc_no;
                                                                @endphp
                                                            @endif
                                                        @endif
                                                    @endfor
                                                    <tr id="rowFKdetail{{ $iRow }}">
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ date_format(date_create($qFK->delivery_order_date), 'd/m/Y') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->delivery_order_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fp_no_dtl{{ $iRow }}" class="col-form-label">{{ $qFK->fp_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qFK->sales_order_no_all,1,strlen($qFK->sales_order_no_all)) }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_before_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_before_vat-$retur_total_before_vat),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($vat,0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qFK->total_after_vat-$retur_total_before_vat),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $totalValbeforeVAT+=($qFK->total_before_vat-$retur_total_before_vat);
                                                        $totalValafterVAT+=($qFK->total_after_vat-$retur_total_before_vat);
                                                    @endphp
                                                @endif
                                                @php
                                                    $iRow+=1;
                                                @endphp
                                            @endif
                                        @endfor
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr id="rowFKdetail-grandtotal">
                                        <td scope="row" colspan="6" style="text-align:right;"><label for="" id="grandtotallbl" class="col-form-label">Grand Total</label></td>
                                        <td scope="row" style="text-align:right;"><label for="" id="grandtotalnumlbl" class="col-form-label">{{ number_format($grandTotalVal,0,'.',',') }}</label></td>
                                        <td scope="row">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="totRowFKdetail" id="totRowFKdetail" value="@if(old('totRowFKdetail')){{ old('totRowFKdetail') }}@else{{ $all_selected_FK_count_from_db }}@endif">
                            <input type="hidden" name="totalValbeforeVAT" id="totalValbeforeVAT" value="@if(old('totalValbeforeVAT')){{ old('totalValbeforeVAT') }}@else{{ $totalValbeforeVAT }}@endif">
                            <input type="hidden" name="totalValafterVAT" id="totalValafterVAT" value="@if(old('totalValafterVAT')){{ old('totalValafterVAT') }}@else{{ $totalValafterVAT }}@endif">
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
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
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#header,#footer',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
});
</script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function addrowFK(fk_id,fk_no){
        let lastCounter = $("#totRowFK").val();
        vHtml = '<tr id="rowFK'+lastCounter+'">'+
            '<td scope="row" style="text-align:right;"><label for="" id="faktur_row_number'+lastCounter+'" class="col-form-label">'+(parseInt(lastCounter)+1)+'.</label></td>'+
            '<td>'+
            '<label for="" name="fk_no'+lastCounter+'" id="fk_no'+lastCounter+'" class="col-form-label">'+fk_no+'</label>'+
            '<input type="hidden" name="fk_id'+lastCounter+'" id="fk_id'+lastCounter+'" value="'+fk_id+'">'+
            '</td>'+
            '<td style="text-align: center;vertical-align: middle;"><input type="checkbox" id="rowFKCheck'+lastCounter+'" value="'+lastCounter+'"></td>'+
            '</tr>';
        $("#new-row-fk").append(vHtml);
        $("#totRowFK").val(parseInt(lastCounter)+1);

        let allFK = $("#all_selected_FK").val()+','+fk_no;
        $("#all_selected_FK").val(allFK);

        // prepare FK detail
        var fd = new FormData();
        fd.append('fk_id', fk_id);
        $.ajax({
            url: "{{ url('/disp_fk') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].delivery_order;
                let p = res[0].nota_retur;
                if (p.total_before_vat===undefined){retur_total_before_vat = 0;}else{retur_total_before_vat = p.total_before_vat;}
                let sales_order_no_all = o.sales_order_no_all;
                if(sales_order_no_all!==''){
                    sales_order_no_all = o.sales_order_no_all.substring(1, o.sales_order_no_all.length);
                    sales_order_no_all = sales_order_no_all.replaceAll(",","<br/>");
                }

                let vatVal = 0;
                let aftervatVal = 0;
                if(o.is_vat==='Y'){
                    vatVal = parseFloat(o.total_after_vat)-parseFloat(o.total_before_vat);
                    aftervatVal = parseFloat(o.total_after_vat-retur_total_before_vat);
                }else{
                    vatVal = 0;
                    aftervatVal = parseFloat(o.total_before_vat-retur_total_before_vat);
                }

                let lastFKdtlCounter = $("#totRowFKdetail").val();
                let doDate = o.delivery_order_date.split("-");
                vHtml = '<tr id="rowFKdetail'+lastFKdtlCounter+'">'+
                    '<td scope="row" style="text-align:left;"><label for="" id="fk_date_dtl'+lastFKdtlCounter+'" class="col-form-label">'+doDate[2]+'/'+doDate[1]+'/'+doDate[0]+'</label></td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="fk_no_dtl'+lastFKdtlCounter+'" class="col-form-label">'+o.delivery_order_no+'</label></td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="fp_no_dtl'+lastFKdtlCounter+'" class="col-form-label">'+o.fp_no+'</label></td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="so_dtl'+lastFKdtlCounter+'" class="col-form-label">'+sales_order_no_all+'</label></td>'+
                    '<td scope="row" style="text-align:right;">'+
                    '<label for="" id="total_before_vat_dtl'+lastFKdtlCounter+'" class="col-form-label">'+parseFloat(o.total_before_vat-retur_total_before_vat).numberFormat(0,'.',',')+'</label>'+
                    '</td>'+
                    '<td scope="row" style="text-align:right;">'+
                    '<label for="" id="vat_dtl'+lastFKdtlCounter+'" class="col-form-label">'+(parseFloat(vatVal)).numberFormat(0,'.',',')+'</label>'+
                    '</td>'+
                    '<td scope="row" style="text-align:right;">'+
                    '<label for="" id="total_after_vat_dtl'+lastFKdtlCounter+'" class="col-form-label">'+parseFloat(aftervatVal).numberFormat(0,'.',',')+'</label>'+
                    '</td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="cust_doc_no_dtl'+lastFKdtlCounter+'" class="col-form-label">-</label></td>'+
                    '</tr>';
                $("#new-rowfk-detail").append(vHtml);
                $("#totRowFKdetail").val(parseInt(lastFKdtlCounter)+1);

                let cust_doc_no_arr = o.sales_order_no_all.split(',');
                let cust_doc_no = '';
                for(let i=0;i<cust_doc_no_arr.length;i++){
                    if(cust_doc_no_arr[i]!==''){
                        var fd_so = new FormData();
                        fd_so.append('so_no', cust_doc_no_arr[i]);
                        $.ajax({
                            url: "{{ url('/disp_so_dtl') }}",
                            type: "POST",
                            enctype: "application/x-www-form-urlencoded",
                            data: fd_so,
                            cache: false,
                            contentType: false,
                            processData: false,
                            dataType: "json",
                            success: function (res_so) {
                                let o = res_so[0].sales_order;
                                cust_doc_no += '<br/>'+o.customer_doc_no;
                                if(parseInt(i)===1){
                                    cust_doc_no = cust_doc_no.substring(5,cust_doc_no.length);
                                }

                                $("#cust_doc_no_dtl"+lastFKdtlCounter).html((o.customer_doc_no==null?'':cust_doc_no));
                            }
                        });
                    }
                }

                addrowGrandTotalFK();
            }
        });
    }

    function addrowGrandTotalFK(){
        let totalValbeforeVAT = 0;
        let totalValafterVAT = 0;
        for(let iRow=0;iRow<$("#totRowFKdetail").val();iRow++){
            if(!isNaN(parseFloat($("#total_before_vat_dtl"+iRow).text().replaceAll(',',''))) &&
                !isNaN(parseFloat($("#total_after_vat_dtl"+iRow).text().replaceAll(',','')))){
                totalValbeforeVAT += parseFloat($("#total_before_vat_dtl"+iRow).text().replaceAll(',',''));
                totalValafterVAT += parseFloat($("#total_after_vat_dtl"+iRow).text().replaceAll(',',''));
            }
        }
        // total before & after vat
        $("#totalValbeforeVAT").val(totalValbeforeVAT);
        $("#totalValafterVAT").val(totalValafterVAT);
        $("#grandtotalnumlbl").text(parseFloat(totalValafterVAT).numberFormat(0,'.',','));
    }

    function dispDObyCustomer(custId){
        $("#new-row-fk").empty();
        $("#delivery_order_id").empty();
        $("#new-row-so").empty();
        $("#delivery_order_id").append(`<option value="#">Choose...</option>`);

        var fd = new FormData();
        fd.append('customer_id', custId);
        $.ajax({
            url: "{{ url('/disp_do') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].delivery_order;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].delivery_order_no;
                        optionValue = o[i].id;
                        $("#delivery_order_id").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            $('#invoice_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#expired_invoice_date').bootstrapMaterialDatePicker({
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
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
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
        @if ($qInv->created_by==Auth::user()->id && $qInv->active=='Y' && strpos($qInv->invoice_no,'Draft')>0)
        $("#del-btn").click(function() {
            let msg = 'The following Invoice Numbers will be canceled.\n{{ $qInv->invoice_no }}\nContinue?';
            if(!confirm(msg)){
                event.preventDefault();
            }else{
                $("#orderId").val('{{ $qInv->id }}');
                $("input[name='_method']").val('POST');
                $('#submit-form').attr('method', "POST");
                $('#submit-form').attr('action', "{{ url('/del_invoice') }}");
                $("#submit-form").submit();
            }
        });
        @endif
        $("#del-row-fk").click(function() {
            let allFK = '';
            for (let i=0; i<$("#totRowFK").val(); i++) {
                if ($("#rowFKCheck"+i).is(':checked')) {
                    let FKno = $("#fk_no"+i).text();
                    $("#rowFK"+i).remove();

                    for (let j=0; j<$("#totRowFKdetail").val(); j++) {
                        if ($("#fk_no_dtl"+j).text()==FKno){
                            $("#rowFKdetail"+j).remove();
                        }
                    }
                }else{
                    allFK += ','+$("#fk_no"+i).text();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totRowFK").val(); i++) {
                if($("#faktur_row_number"+i).text()){
                    $("#faktur_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
            
            $("#all_selected_FK").val(allFK);
            addrowGrandTotalFK();
        });

        $("#gen-fk").click(function() {
            let fk_id = $("#delivery_order_id option:selected").val();
            let fk_no = $("#delivery_order_id option:selected").text();
            for(let i=0;i<$("#totRowFK").val();i++){
                if($("#fk_no"+i).text()===fk_no){
                    alert('The FK number already exists.');
                    return false;
                }
            }
            if(fk_id!=='#'){
                addrowFK(fk_id,fk_no);
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totRowFK").val(); i++) {
                if($("#faktur_row_number"+i).text()){
                    $("#faktur_row_number"+i).text(j+'. ');
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
    });
</script>
@endsection
