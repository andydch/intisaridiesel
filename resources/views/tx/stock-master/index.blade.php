@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .cell-style-header {
        border: 1px solid #fff;
        padding: 5px;
        font-weight: bold;
    }
    .cell-style {
        border: 1px solid #fff;
        padding: 5px;
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
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="form_submit" id="form_submit" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="part_no" class="col-sm-3 col-form-label">Part No</label>
                                <div class="col-sm-9">
                                    <input type="text" name="part_no" id="part_no" maxlength="255"
                                        class="form-control @error('part_no') is-invalid @enderror"
                                        value="@isset($request){{ $request->part_no }}@endisset">
                                    @error('part_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="part_name" class="col-sm-3 col-form-label">Part Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="part_name" id="part_name" maxlength="255"
                                        class="form-control @error('part_name') is-invalid @enderror"
                                        value="@isset($request){{ $request->part_name }}@endisset">
                                    @error('part_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="brand_id" class="col-sm-3 col-form-label">Brand</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('brand_id') is-invalid @enderror"
                                        id="brand_id" name="brand_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $brand_id = isset($request)?$request->brand_id:0;
                                        @endphp
                                        @foreach ($queryBrand as $p)
                                            <option @if ($brand_id==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->string_val }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="brandtype_id" class="col-sm-3 col-form-label">Brand Type</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('brandtype_id') is-invalid @enderror"
                                        id="brandtype_id" name="brandtype_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $brandtype_id = isset($request)?$request->brandtype_id:0;
                                        @endphp
                                        @foreach ($queryBrandType as $p)
                                            <option @if ($brandtype_id==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->brand_type }}</option>
                                        @endforeach
                                    </select>
                                    @error('brandtype_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="partType_id" class="col-sm-3 col-form-label">Part Type</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('partType_id') is-invalid @enderror"
                                        id="brand_id" name="partType_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $partType_id = isset($request)?$request->partType_id:0;
                                        @endphp
                                        @foreach ($queryPartType as $p)
                                            <option @if ($partType_id==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->string_val }}</option>
                                        @endforeach
                                    </select>
                                    @error('partType_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                        id="branch_id" name="branch_id">
                                        <option value="">All</option>
                                        @php
                                            $branch_id = isset($request)?$request->branch_id:0;
                                        @endphp
                                        @foreach ($queryBranch as $p)
                                            <option @if ($branch_id==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">Show Cost</label>
                                <div class="col-sm-9">
                                    @php
                                        $checked = '';
                                    @endphp
                                    @isset($request)
                                        @if($request->showCost=='on')
                                            @php
                                                $checked = 'checked';
                                            @endphp
                                        @endif
                                    @endisset
                                    <input class="form-check-input" type="checkbox" id="showCost" name="showCost" {{ $checked }} aria-label="Show Cost">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-light px-5">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/create') }}" style="margin-bottom: 15px;">Add</a>
                <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Delete</a>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                        @endif
                        @if (session('status-error'))
                        <div class="alert alert-danger">
                            {{ session('status-error') }}
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="stock-master-list" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Part Number</th>
                                        <th>Part Name</th>
                                        <th>Part Type</th>
                                        <th>Branch</th>
                                        {{-- <th>Brand</th> --}}
                                        <th>OH</th>
                                        <th>SO</th>
                                        <th>OO</th>
                                        <th>IT</th>
                                        <th>Unit</th>
                                        @if ($checked=='checked')
                                            <th>Final Cost</th>
                                            <th>AVG Cost</th>
                                        @endif
                                        <th>Final Price</th>
                                        <th>Price List</th>
                                        <th>Action</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($stocks as $o)
                                        <tr>
                                            <td>
                                                @php
                                                    $partNumber = $o->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                {{ $partNumber }}
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $o->part_name }}">
                                                <input type="hidden" name="part_id{{ $i }}" id="part_id{{ $i }}" value="{{ $o->part_idx }}">
                                            </td>
                                            <td>{{ $o->part_name }}</td>
                                            <td>{{ $o->part_type_name }}</td>
                                            <td>
                                                @php
                                                    $branch_id = $o->branch_id_tmp;
                                                @endphp
                                                @if (!is_null($o->branch_name))
                                                    {{ $o->branch_name }}
                                                @else
                                                    {{-- {{ $o->purchase_ro_branch_id }} --}}
                                                    @php
                                                        $qBranch = \App\Models\Mst_branch::where('id','=',$o->purchase_ro_branch_id)
                                                        ->where([
                                                            'active' => 'Y'
                                                        ])
                                                        ->first();
                                                        if($qBranch){
                                                            $branch_id = $qBranch->id;
                                                            echo $qBranch->name;
                                                        }
                                                    @endphp
                                                @endif
                                            </td>
                                            {{-- <td>{{ $o->brand_name }}</td> --}}
                                            <td style="text-align: right;">{{ (!is_null($o->qty)?$o->qty:0) }}</td>
                                            @php
                                                // sales order
                                                $qtySO = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                                ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                                ->whereNotIn('txso.id',function (\Illuminate\Database\Query\Builder $query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                    ->where('tx_do_parts.active','=','Y')
                                                    ->where('tx_do.active','=','Y');
                                                })
                                                ->where('tx_sales_order_parts.part_id','=',$o->part_idx)
                                                ->where('tx_sales_order_parts.active','=','Y')
                                                ->where('txso.sales_order_no','NOT LIKE','%Draft%')
                                                ->where('txso.need_approval','=','N')
                                                ->where('txso.active','=','Y')
                                                ->when($branch_id!=null, function($q) use($branch_id) {
                                                    $q->whereRaw('((usr.branch_id='.$branch_id.' AND txso.branch_id IS null) OR txso.branch_id='.$branch_id.')');
                                                })
                                                // ->when($branch_id==null, function($q) use($branch_id) {
                                                //     $q->where('usr.branch_id','=',$branch_id);
                                                // })
                                                ->sum('tx_sales_order_parts.qty');

                                                // surat jalan
                                                $qtySJ = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
                                                ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
                                                ->whereNotIn('txsj.id',function (\Illuminate\Database\Query\Builder $query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                    ->where('tx_do_parts.active','=','Y')
                                                    ->where('tx_do.active','=','Y');
                                                })
                                                ->where('tx_surat_jalan_parts.part_id','=',$o->part_idx)
                                                ->where('tx_surat_jalan_parts.active','=','Y')
                                                ->where('txsj.surat_jalan_no','NOT LIKE','%Draft%')
                                                ->where('txsj.need_approval','=','N')
                                                ->where('txsj.active','=','Y')
                                                ->when($branch_id!=null, function($q) use($branch_id) {
                                                    $q->whereRaw('((usr.branch_id='.$branch_id.' AND txsj.branch_id IS null) OR txsj.branch_id='.$branch_id.')');
                                                })
                                                // ->when($branch_id==null, function($q) use($branch_id) {
                                                //     $q->where('usr.branch_id','=',$branch_id);
                                                // })
                                                ->sum('tx_surat_jalan_parts.qty');
                                            @endphp
                                            <td style="text-align: right;">
                                                @if (($qtySO+$qtySJ)!=0)
                                                    <a href="#" onclick=dispSalesOrderInfo({{ $o->part_idx }},{{ $branch_id }});>{{ ($qtySO+$qtySJ) }}</a>
                                                @else
                                                    {{ ($qtySO+$qtySJ) }}
                                                @endif
                                            </td>
                                            @php
                                                // on order
                                                $oo = ((is_null($o->purchase_memo_qty)?0:$o->purchase_memo_qty)+$o->purchase_order_qty)-($o->purchase_ro_qty_mo+$o->purchase_ro_qty_po+$o->purchase_ro_qty_no_partial_mo+$o->purchase_ro_qty_no_partial_po);
                                            @endphp
                                            <input type="hidden" name="memo" value="{{ is_null($o->purchase_memo_qty)?0:$o->purchase_memo_qty }}">
                                            <input type="hidden" name="porder" value="{{ $o->purchase_order_qty }}">
                                            <input type="hidden" name="ro" value="{{ $o->purchase_ro_qty }}">
                                            <input type="hidden" name="ro_nopartial" value="{{ $o->purchase_ro_qty_no_partial }}">
                                            <input type="hidden" name="branch_idx" value="{{ $o->branch_id_tmp }}">
                                            <td style="text-align: right;">
                                                @if($oo!=0)
                                                    <a href="#" onclick=dispOnOrderInfo({{ $o->part_idx }},{{ $branch_id }});>{{ $oo }}</a>
                                                @else
                                                    {{ $oo }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($o->in_transit_qty!=0)
                                                    <a href="#" onclick=dispInTransitInfo({{ $o->part_idx }},{{ $branch_id }});>{{ (!is_null($o->in_transit_qty)?$o->in_transit_qty:0) }}</a>
                                                @else
                                                    {{ (!is_null($o->in_transit_qty)?$o->in_transit_qty:0) }}
                                                @endif
                                            </td>
                                            <td>{{ $o->unit_name }}</td>
                                            @if ($checked=='checked')
                                                <td style="text-align: right;">
                                                    @if ($o->purchase_ro_final_cost>0)
                                                        {{ number_format($o->purchase_ro_final_cost,0,'.',',') }}
                                                    @else
                                                        {{ number_format($o->purchase_ro_qty_no_partial_final_cost,0,'.',',') }}
                                                    @endif
                                                </td>
                                                <td style="text-align: right;">{{ number_format($o->avg_cost,0,'.',',') }}</td>
                                            @endif
                                            <td style="text-align: right;">{{ number_format($o->last_final_price,0,'.',',') }}</td>
                                            {{-- <td style="text-align: right;">{{ number_format($o->final_price,0,'.',',') }}</td> --}}
                                            <td style="text-align: right;">{{ number_format($o->price_list,0,'.',',') }}</td>
                                            <td>
                                                <a style="text-decoration: underline;" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($o->slug)).'?br_id='.$branch_id }}">View</a> |
                                                <a style="text-decoration: underline;" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($o->slug).'/edit') }}">Edit</a> |
                                                <a style="text-decoration: underline;" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri_folder.'/'.$o->part_idx) }}">Stock Card</a>
                                            </td>
                                            <td>
                                                @php
                                                    $isTx = false;

                                                    $tx01 = \App\Models\Tx_delivery_order_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx01){$isTx = true;}

                                                    $tx02 = \App\Models\Tx_nota_retur_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx02 && !$isTx){$isTx = true;}

                                                    $tx03 = \App\Models\Tx_purchase_memo_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx03 && !$isTx){$isTx = true;}

                                                    $tx04 = \App\Models\Tx_purchase_order_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx04 && !$isTx){$isTx = true;}

                                                    $tx05 = \App\Models\Tx_purchase_quotation_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx05 && !$isTx){$isTx = true;}

                                                    $tx06 = \App\Models\Tx_purchase_retur_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx06 && !$isTx){$isTx = true;}

                                                    $tx07 = \App\Models\Tx_receipt_order_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx07 && !$isTx){$isTx = true;}

                                                    $tx08 = \App\Models\Tx_sales_order_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx08 && !$isTx){$isTx = true;}

                                                    $tx09 = \App\Models\Tx_sales_quotation_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx09 && !$isTx){$isTx = true;}

                                                    $tx10 = \App\Models\Tx_stock_assembly_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx10 && !$isTx){$isTx = true;}

                                                    $tx11 = \App\Models\Tx_stock_disassembly_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx11 && !$isTx){$isTx = true;}

                                                    $tx12 = \App\Models\Tx_stock_transfer_part::where([
                                                        'part_id' => $o->part_idx,
                                                    ])
                                                    ->first();
                                                    if($tx12 && !$isTx){$isTx = true;}
                                                @endphp
                                                @if ($o->part_active=='Y' && !$isTx)
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}" style="margin: auto;display:block;">
                                                @else
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th>Part Number</th>
                                        <th>Part Name</th>
                                        <th>Part Type</th>
                                        <th>Branch</th>
                                        <th>Brand</th>
                                        <th>OH</th>
                                        <th>SO</th>
                                        <th>OO</th>
                                        <th>IT</th>
                                        <th>Unit</th>
                                        @if ($checked=='checked')
                                            <th>Final Cost</th>
                                            <th>AVG Cost</th>
                                        @endif
                                        <th>Final Price</th>
                                        <th>Price List</th>
                                        <th>Action</th>
                                        <th>Delete</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="all_ids" id="all_ids">
        </form>
    </div>
</div>
<!--end page wrapper -->

<!-- Full screen modal -->
<div class="modal fade" id="related-info" aria-hidden="true" aria-labelledby="related-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="related-info-title">Related Information</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="msg-modal"></span>
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to first</button>
            </div> --}}
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    function dispInfo(){
        $("#related-info").modal('show');
    }
    function dispSalesOrderInfo(part_id,branch_id){
        $("#msg-modal").text('');

        var fd = new FormData();
        fd.append('part_id',part_id);
        fd.append('branch_id',branch_id);
        $.ajax({
            url: '{{ url("disp-so-stock-master") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].sales_orders;
                let p = res[0].surat_jalans;
                let totSO = o.length;
                let totSJ = p.length;
                let vSO = '';
                let vSJ = '';
                if (totSO > 0) {
                    for (let i = 0; i < totSO; i++) {
                        let objectDate = new Date(o[i].sales_order_date);
                        vSO += '<tr>'+
                            '<td class="cell-style">'+o[i].sales_order_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+objectDate.getMonth()+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+o[i].cust_name+'</td>'+
                            '<td class="cell-style">'+o[i].so_qty+'</td>'+
                        '</tr>';
                    }
                }
                if (totSJ > 0) {
                    for (let i = 0; i < totSJ; i++) {
                        let objectDate = new Date(p[i].surat_jalan_date);
                        vSJ += '<tr>'+
                            '<td class="cell-style">'+p[i].surat_jalan_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+objectDate.getMonth()+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+p[i].cust_name+'</td>'+
                            '<td class="cell-style">'+p[i].sj_qty+'</td>'+
                        '</tr>';
                    }
                }
                if(vSO!=='' || vSJ!==''){
                    vSO = vSO+vSJ;
                    let vHtml = '<table style="width: 100%;">'+
                        '<thead>'+
                            '<tr>'+
                                '<td class="cell-style-header">SO & SJ No</td>'+
                                '<td class="cell-style-header">Date</td>'+
                                '<td class="cell-style-header">Customer</td>'+
                                '<td class="cell-style-header">Qty</td>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>'+vSO+'</tbody>'+
                        '</table>';
                    $("#msg-modal").html(vHtml);
                    $("#related-info").modal('show');
                }
            },
        });
    }
    function dispOnOrderInfo(part_id,branch_id){
        $("#msg-modal").text('');

        var fd = new FormData();
        fd.append('part_id',part_id);
        fd.append('branch_id',branch_id);
        $.ajax({
            url: '{{ url("disp-oo-stock-master") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let memo = res[0].memo;
                let totMemo = memo.length;
                let vMemo = '';
                if(totMemo>0){
                    for (let i = 0; i < totMemo; i++) {
                        let objectDate = new Date(memo[i].memo_date);
                        vMemo += '<tr>'+
                            '<td class="cell-style">'+memo[i].memo_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+objectDate.getMonth()+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+memo[i].supplier_name+'</td>'+
                            '<td class="cell-style">'+(parseInt(memo[i].memo_qty)+parseInt(memo[i].purchase_ro_qty)-parseInt(memo[i].purchase_ro_qty_no_partial))+'</td>'+
                            // '<td class="cell-style">'+memo[i].memo_qty+'-('+memo[i].purchase_ro_qty+'+'+memo[i].purchase_ro_qty_no_partial+')'+'</td>'+
                        '</tr>';
                    }
                }

                let po = res[0].po;
                let totPo = po.length;
                let vPo = '';
                if(totPo>0){
                    for (let i = 0; i < totPo; i++) {
                        let objectDate = new Date(po[i].purchase_date);
                        vPo += '<tr>'+
                            '<td class="cell-style">'+po[i].purchase_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+objectDate.getMonth()+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+po[i].supplier_name+'</td>'+
                            '<td class="cell-style">'+(parseInt(po[i].pr_order_qty)-parseInt(po[i].purchase_ro_qty)-parseInt(po[i].purchase_ro_qty_no_partial))+'</td>'+
                            // '<td class="cell-style">'+po[i].pr_order_qty+'-('+po[i].purchase_ro_qty+'+'+po[i].purchase_ro_qty_no_partial+')'+'</td>'+
                        '</tr>';
                    }
                }

                if(totMemo>0 || totPo>0){
                    let vO = vMemo+vPo;
                    let vHtml = '<table style="width: 100%;">'+
                        '<thead>'+
                            '<tr>'+
                                '<td class="cell-style-header">PO & MO No</td>'+
                                '<td class="cell-style-header">Date</td>'+
                                '<td class="cell-style-header">Supplier</td>'+
                                '<td class="cell-style-header">Qty</td>'+
                            '</tr>'+
                        '</thead>'+
                        '<tbody>'+vO+'</tbody>'+
                        '</table>';
                    $("#msg-modal").html(vHtml);
                    $("#related-info").modal('show');
                }
            },
        });
    }
    function dispInTransitInfo(part_id,branch_id){
        $("#msg-modal").text('');

        var fd = new FormData();
        fd.append('part_id',part_id);
        fd.append('branch_id',branch_id);
        $.ajax({
            url: '{{ url("disp-it-stock-master") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].in_transits;
                let totIT = o.length;
                if (totIT > 0) {
                    let vIT = '';
                    for (let i = 0; i < totIT; i++) {
                        // vIT += ', '+o[i].stock_transfer_no;

                        let objectDate = new Date(o[i].stock_transfer_date);
                        vIT += '<tr>'+
                            '<td class="cell-style">'+o[i].stock_transfer_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+objectDate.getMonth()+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+o[i].branch_from+'</td>'+
                            '<td class="cell-style">'+o[i].branch_to+'</td>'+
                            '<td class="cell-style">'+o[i].it_qty+'</td>'+
                        '</tr>';
                    }
                    if(vIT!==''){
                        // vIT = vIT.substring(2,vIT.length);
                        // $("#msg-modal").text('Stock Master: '+vIT);
                        let vHtml = '<table style="width: 100%;">'+
                            '<thead>'+
                                '<tr>'+
                                    '<td class="cell-style-header">SM No</td>'+
                                    '<td class="cell-style-header">Date</td>'+
                                    '<td class="cell-style-header">From</td>'+
                                    '<td class="cell-style-header">To</td>'+
                                    '<td class="cell-style-header">Qty</td>'+
                                '</tr>'+
                            '</thead>'+
                            '<tbody>'+vIT+'</tbody>'+
                            '</table>';
                        $("#msg-modal").html(vHtml);
                        $("#related-info").modal('show');
                    }
                }
            },
        });
    }
    $(document).ready(function() {
        $('#stock-master-list').DataTable({
            "ordering": false
        });

        $("#part_no").keyup(function() {
            let part_no = $("#part_no").val();
            $("#part_no").val(part_no.toUpperCase());
        });
        $("#part_name").keyup(function() {
            let part_name = $("#part_name").val();
            $("#part_name").val(part_name.toUpperCase());
        });

        $("#btn-del-row").click(function() {
            let rowNo = '';
            for (i = 0; i < {{ $rowCount }}; i++) {
                if ($("#delRow" + i).is(':checked')) {
                    rowNo += '- '+$("#title_caption" + i).val()+'\n';
                }
            }
            if(rowNo!=''){
                let msg = 'The following Part Name will be deleted.\n'+rowNo+'\nProcess cannot be undone. Continue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    let aId = '';
                    for (i = 0; i < {{ $rowCount }}; i++) {
                        if ($("#delRow" + i).is(':checked')) {
                            aId += $("#part_id" + i).val()+',';
                        }
                    }
                    if(aId!==''){
                        $("#all_ids").val(aId);
                        $("#form_submit").attr("action", "{{ url('/del_mstock') }}");
                        $("#form_submit").attr("method", "POST");
                        $("#form_submit").submit();
                    }
                }
            }
        });
    });
</script>
@endsection
