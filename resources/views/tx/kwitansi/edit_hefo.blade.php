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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'-hefo/'.$qKwi->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                                <label for="" class="col-sm-3 col-form-label">KW No</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qKwi->kwitansi_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qKwi->customer->customer_unique_code.' - '.$qKwi->customer->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">NP No</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 97%;text-align:center;">NP No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-fk">
                                                            @php
                                                                $iRow=0;
                                                                $all_selected_NP=explode(",",$all_selected_NP_from_db);
                                                            @endphp
                                                            @for ($lastCounter=0;$lastCounter<count($all_selected_NP);$lastCounter++)
                                                                @if ($all_selected_NP[$lastCounter]!='')
                                                                    @php
                                                                        $iRow+=1;
                                                                        $qNP = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','=',$all_selected_NP[$lastCounter])
                                                                        ->first();
                                                                    @endphp
                                                                    <tr id="rowFK{{ $lastCounter }}">
                                                                        <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $iRow }}.</label></td>
                                                                        <td>
                                                                            <label for="" name="fk_no{{ $lastCounter }}" id="fk_no{{ $lastCounter }}"
                                                                                class="col-form-label">{{ $all_selected_NP[$lastCounter] }}</label>
                                                                            <input type="hidden" name="fk_id{{ $lastCounter }}" id="fk_id{{ $lastCounter }}" value="{{ $lastCounter }}">
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                    {{-- <input type="button" id="del-row-fk" class="btn btn-light px-5" value="Remove Row" style="margin-top: 5px;"> --}}
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Payment To</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qKwi->coa)?$qKwi->coa->coa_name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Plan Date</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qKwi->invoice_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qKwi->branch)?$qKwi->branch->name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="header" class="col-sm-3 col-form-label">Header</label>
                                                <div class="col-sm-9">
                                                    <textarea name="header" id="header" rows="3" class="form-control @error('header') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('header')){{ old('header') }}@else{{ $qKwi->header }}@endif</textarea>
                                                    @error('header')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="footer" class="col-sm-3 col-form-label">Footer</label>
                                                <div class="col-sm-9">
                                                    <textarea name="footer" id="footer" rows="3" class="form-control @error('footer') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('footer')){{ old('footer') }}@else{{ $qKwi->footer }}@endif</textarea>
                                                    @error('footer')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="footer" class="col-sm-3 col-form-label">Paid for</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@else{{ $qKwi->remark }}@endif</textarea>
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
                            <input type="hidden" name="totalValbeforeVAT" id="totalValbeforeVAT" value="@if(old('totalValbeforeVAT')){{ old('totalValbeforeVAT') }}@else{{ 0 }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col">Date</th>
                                        <th scope="col">NP No</th>
                                        <th scope="col">SJ No</th>
                                        <th scope="col">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col">Cust Doc No</th>
                                    </tr>
                                </thead>
                                <tbody id="new-rownp-detail">
                                    @php
                                        $grandTotalVal=0;
                                    @endphp
                                    @if (old('all_selected_NP'))
                                        @php
                                            $iRow=0;
                                        @endphp
                                        @for ($lastCounter=0;$lastCounter<count($all_selected_NP);$lastCounter++)
                                            @if ($all_selected_NP[$lastCounter]!='')
                                                @php
                                                    $qNP = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','=',$all_selected_NP[$lastCounter])
                                                    ->first();
                                                @endphp
                                                @if ($qNP)
                                                    @php
                                                        // nota retur - begin
                                                        $retur_total_price = 0;
                                                        $nota_retur = \App\Models\Tx_nota_retur_non_tax::select(
                                                            'total_price'
                                                        )
                                                        ->whereRaw('approved_by IS NOT null')
                                                        ->where([
                                                            'delivery_order_id'=>$qNP->id,
                                                            'active'=>'Y',
                                                        ])
                                                        ->first();
                                                        if ($nota_retur){
                                                            $retur_total_price = $nota_retur->total_price;
                                                        }
                                                        // nota retur - end

                                                        $all_cust_doc_no_arr=explode(",",$qNP->sales_order_no_all);
                                                        $all_cust_doc_no='';
                                                        $grandTotalVal+=($qNP->total_price-$retur_total_price);
                                                    @endphp
                                                    @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                        @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                            @php
                                                                $so = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_cust_doc_no_arr[$c_doc])
                                                                ->first();
                                                            @endphp
                                                            @if ($so)
                                                                @php
                                                                    $all_cust_doc_no.=','.$so->customer_doc_no;
                                                                @endphp
                                                            @endif
                                                        @endif
                                                    @endfor
                                                    <tr id="rowNPdetail{{ $iRow }}">
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ date_format(date_create($qNP->delivery_order_date),"d/m/Y") }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="np_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qNP->sales_order_no_all,1,strlen($qNP->sales_order_no_all)) }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qNP->total_price-$retur_total_price),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                        </td>
                                                    </tr>
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
                                        @for ($lastCounter=0;$lastCounter<count($all_selected_NP);$lastCounter++)
                                            @if ($all_selected_NP[$lastCounter]!='')
                                                @php
                                                    $qNP = \App\Models\Tx_delivery_order_non_tax::where('tx_delivery_order_non_taxes.delivery_order_no','=',$all_selected_NP[$lastCounter])
                                                    ->first();
                                                @endphp
                                                @if ($qNP)
                                                    @php
                                                        // nota retur - begin
                                                        $retur_total_price = 0;
                                                        $nota_retur = \App\Models\Tx_nota_retur_non_tax::select(
                                                            'total_price'
                                                        )
                                                        ->whereRaw('approved_by IS NOT null')
                                                        ->where([
                                                            'delivery_order_id'=>$qNP->id,
                                                            'active'=>'Y',
                                                        ])
                                                        ->first();
                                                        if ($nota_retur){
                                                            $retur_total_price = $nota_retur->total_price;
                                                        }
                                                        // nota retur - end

                                                        $all_cust_doc_no_arr=explode(",",$qNP->sales_order_no_all);
                                                        $all_cust_doc_no='';
                                                        $grandTotalVal+=($qNP->total_price-$retur_total_price);
                                                    @endphp
                                                    @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                                        @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                            @php
                                                                $so = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_cust_doc_no_arr[$c_doc])
                                                                ->first();
                                                            @endphp
                                                            @if ($so)
                                                                @php
                                                                    $all_cust_doc_no.=','.$so->customer_doc_no;
                                                                @endphp
                                                            @endif
                                                        @endif
                                                    @endfor
                                                    <tr id="rowNPdetail{{ $iRow }}">
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ date_format(date_create($qNP->delivery_order_date),"d/m/Y") }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="np_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qNP->sales_order_no_all,1,strlen($qNP->sales_order_no_all)) }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qNP->total_price-$retur_total_price),0,'.',',') }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                                        </td>
                                                    </tr>
                                                @endif
                                                @php
                                                    $iRow+=1;
                                                @endphp
                                            @endif
                                        @endfor
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr id="rowNPdetail-grandtotal">
                                        <td scope="row" colspan="3" style="text-align:right;"><label for="" id="grandtotallbl" class="col-form-label">Grand Total</label></td>
                                        <td scope="row" style="text-align:right;">
                                            <label for="" id="grandtotalnumlbl" class="col-form-label">{{ number_format($grandTotalVal,0,'.',',') }}</label>
                                            <input type="hidden" name="totalValafterVAT" id="totalValafterVAT" value="@if(old('totalValafterVAT')){{ old('totalValafterVAT') }}@else{{ $grandTotalVal }}@endif">
                                        </td>
                                        <td scope="row">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="totRowNPdetail" id="totRowNPdetail" value="@if(old('totRowNPdetail')){{ old('totRowNPdetail') }}@else{{ $iRow }}@endif">
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($qKwi->created_by==Auth::user()->id && $qKwi->active=='Y' && (strpos($qKwi->kwitansi_no,'Draft')>0 || is_null($qKwi->approved_by)))
                                        @if (strpos($qKwi->kwitansi_no,'Draft')>0)
                                            <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                        @endif
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                        <input type="hidden" name="orderId" id="orderId">
                                    @else
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
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
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#header,#footer',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
});
</script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function addrowNP(np_id,np_no){
        let lastCounter = $("#totRowNP").val();
        vHtml = '<tr id="rowNP'+lastCounter+'">'+
            '<td scope="row" style="text-align:right;"><label for="" id="nota-penjualan'+lastCounter+'" class="col-form-label">'+(parseInt(lastCounter)+1)+'.</label></td>'+
            '<td>'+
            '<label for="" name="np_no'+lastCounter+'" id="np_no'+lastCounter+'" class="col-form-label">'+np_no+'</label>'+
            '<input type="hidden" name="np_id'+lastCounter+'" id="np_id'+lastCounter+'" value="'+np_id+'">'+
            '</td>'+
            '<td style="text-align: center;"><input type="checkbox" id="rowNPCheck'+lastCounter+'" value="'+lastCounter+'"></td>'+
            '</tr>';
        $("#new-row-np").append(vHtml);
        $("#totRowNP").val(parseInt(lastCounter)+1);

        let allFK = $("#all_selected_NP").val()+','+np_no;
        $("#all_selected_NP").val(allFK);

        // prepare FK detail
        var fd = new FormData();
        fd.append('np_id', np_id);
        $.ajax({
            url: "{{ url('/disp_np') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let retur_total_price = 0;
                let o = res[0].delivery_order;
                let p = res[0].nota_retur;
                if (p.total_price===undefined){retur_total_price = 0;}else{retur_total_price = p.total_price;}

                let sales_order_no_all = o.sales_order_no_all;
                if(sales_order_no_all!=='' && sales_order_no_all!==null){
                    sales_order_no_all = o.sales_order_no_all.substring(1, o.sales_order_no_all.length);
                    sales_order_no_all = sales_order_no_all.replaceAll(",","<br/>");
                }

                let vatVal = 0;
                let aftervatVal = parseFloat(o.total_price-retur_total_price);
                let lastFKdtlCounter = $("#totRowNPdetail").val();
                let doDate = o.delivery_order_date.split("-");
                vHtml = '<tr id="rowNPdetail'+lastFKdtlCounter+'">'+
                    '<td scope="row" style="text-align:left;"><label for="" id="fk_date_dtl'+lastFKdtlCounter+'" class="col-form-label">'+doDate[2]+'/'+doDate[1]+'/'+doDate[0]+'</label></td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="np_no_dtl'+lastFKdtlCounter+'" class="col-form-label">'+o.delivery_order_no+'</label></td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="so_dtl'+lastFKdtlCounter+'" class="col-form-label">'+sales_order_no_all+'</label></td>'+
                    '<td scope="row" style="text-align:right;">'+
                    '<label for="" id="total_after_vat_dtl'+lastFKdtlCounter+'" class="col-form-label">'+parseFloat(aftervatVal).numberFormat(0,'.',',')+'</label>'+
                    '</td>'+
                    '<td scope="row" style="text-align:left;"><label for="" id="cust_doc_no_dtl'+lastFKdtlCounter+'" class="col-form-label">-</label></td>'+
                    '</tr>';
                $("#new-rownp-detail").append(vHtml);
                $("#totRowNPdetail").val(parseInt(lastFKdtlCounter)+1);

                let cust_doc_no_arr = o.sales_order_no_all.split(',');
                let cust_doc_no = '';
                for(let i=0;i<cust_doc_no_arr.length;i++){
                    if(cust_doc_no_arr[i]!==''){
                        var fd_so = new FormData();
                        fd_so.append('so_no', cust_doc_no_arr[i]);
                        $.ajax({
                            url: "{{ url('/disp_sj_dtl') }}",
                            type: "POST",
                            enctype: "application/x-www-form-urlencoded",
                            data: fd_so,
                            cache: false,
                            contentType: false,
                            processData: false,
                            dataType: "json",
                            success: function (res_so) {
                                let o = res_so[0].surat_jalan;
                                // if(strpos('<br/>'+o.customer_doc_no,"null")>0){
                                //     cust_doc_no += '<br/>'+o.customer_doc_no;
                                // }
                                if (o.customer_doc_no!=null){
                                    cust_doc_no += '<br/>'+o.customer_doc_no;
                                }
                                if(parseInt(i)===1){
                                    cust_doc_no = cust_doc_no.substring(5,cust_doc_no.length);
                                }

                                $("#cust_doc_no_dtl"+lastFKdtlCounter).html(cust_doc_no);
                            }
                        });
                    }
                }

                addrowGrandTotalNP();
            }
        });
    }

    function addrowGrandTotalNP(){
        let totalValbeforeVAT = 0;
        let totalValafterVAT = 0;
        for(let iRow=0;iRow<$("#totRowNPdetail").val();iRow++){
            if(!isNaN(parseFloat($("#total_after_vat_dtl"+iRow).text().replaceAll(',','')))){
                totalValafterVAT += parseFloat($("#total_after_vat_dtl"+iRow).text().replaceAll(',',''));
            }
        }
        $("#totalValafterVAT").val(totalValafterVAT);
        $("#grandtotalnumlbl").text(parseFloat(totalValafterVAT).numberFormat(0,'.',','));
    }

    function dispDObyCustomer(custId,kwId){
        $("#new-row-np").empty();
        $("#delivery_order_id").empty();
        $("#new-row-np").empty();
        $("#delivery_order_id").append(`<option value="#">Choose...</option>`);

        var fd = new FormData();
        fd.append('customer_id', custId);
        fd.append('kwitansi_id', kwId);
        $.ajax({
            url: "{{ url('/disp_do_non_tax') }}",
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
            $('#kwitansi_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                // format: 'YYYY-MM-DD HH:mm',
                time: false
            });
            $('#expired_kwitansi_date').bootstrapMaterialDatePicker({
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
        @if ($qKwi->created_by==Auth::user()->id && $qKwi->active=='Y' && (strpos($qKwi->kwitansi_no,'Draft')>0 || is_null($qKwi->approved_by)))
            $("#del-btn").click(function() {
                let msg = 'The following Invoice Numbers will be canceled.\n{{ $qKwi->kwitansi_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#orderId").val('{{ $qKwi->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_kwitansi') }}");
                    $("#submit-form").submit();
                }
            });
        @endif
        $("#del-row-np").click(function() {
            let allFK = '';
            for (let i=0; i<$("#totRowNP").val(); i++) {
                if ($("#rowNPCheck"+i).is(':checked')) {
                    let NPno = $("#np_no"+i).text();
                    $("#rowNP"+i).remove();

                    for (let j=0; j<$("#totRowNPdetail").val(); j++) {
                        if ($("#np_no_dtl"+j).text()==NPno){
                            $("#rowNPdetail"+j).remove();
                        }
                    }
                }else{
                    allFK += ','+$("#np_no"+i).text();
                }
            }
            $("#all_selected_NP").val(allFK);

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowNP").val(); i++) {
                if($("#nota-penjualan"+i).text()){
                    $("#nota-penjualan"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
            
            addrowGrandTotalNP();
        });

        $("#gen-np").click(function() {
            let np_id = $("#delivery_order_id option:selected").val();
            let np_no = $("#delivery_order_id option:selected").text();
            for(let i=0;i<$("#totRowNP").val();i++){
                if($("#np_no"+i).text()===np_no){
                    alert('The NP number already exists.');
                    return false;
                }
            }
            if(np_id!=='#'){
                addrowNP(np_id,np_no);
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowNP").val(); i++) {
                if($("#nota-penjualan"+i).text()){
                    $("#nota-penjualan"+i).text(j+'. ');
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
