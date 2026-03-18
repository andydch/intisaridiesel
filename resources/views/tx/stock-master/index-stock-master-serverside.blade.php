@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .cell-style-header {
        border: 1px solid #fff;
        padding: 5px;
        font-weight: bold;
        color:white;
    }
    .cell-style {
        border: 1px solid #fff;
        padding: 5px;
        color:white;
    }
    .text-right {
        text-align: right;
    }
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
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="form_submit" id="form_submit" action="{{ route('stockmaster.store') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="part_no" class="col-sm-3 col-form-label">Part No</label>
                                <div class="col-sm-9">
                                    <input type="text" name="part_no" id="part_no" maxlength="255" class="form-control @error('part_no') is-invalid @enderror"
                                        value="{{ $parameter[0] }}">
                                    @error('part_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="part_name" class="col-sm-3 col-form-label">Part Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="part_name" id="part_name" maxlength="255" class="form-control @error('part_name') is-invalid @enderror"
                                        value="{{ $parameter[1] }}">
                                    @error('part_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="brand_id" class="col-sm-3 col-form-label">Brand</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $brand_id = $parameter[2];
                                        @endphp
                                        @foreach ($queryBrand as $p)
                                            <option @if ($brand_id==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->title_ind }}</option>
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
                                    <select class="form-select single-select @error('brandtype_id') is-invalid @enderror" id="brandtype_id" name="brandtype_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $brandtype_id = $parameter[3];
                                            $queryBrandType = \App\Models\Mst_brand_type::where([
                                                'brand_id' => $brand_id,
                                                'active' => 'Y',
                                            ])
                                            ->orderBy('brand_type','ASC')
                                            ->get();
                                        @endphp
                                        @foreach ($queryBrandType as $p)
                                            <option @if ($brandtype_id==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->brand_type }}</option>
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
                                    <select class="form-select single-select @error('partType_id') is-invalid @enderror" id="partType_id" name="partType_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $partType_id = $parameter[4];
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
                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                        <option value="">All</option>
                                        @php
                                            $branch_id = $parameter[5];
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
                                <label for="showCost" class="col-sm-3 col-form-label">Show Cost</label>
                                <div class="col-sm-9">
                                    @php
                                        $checked = '';
                                    @endphp
                                    @if($parameter[6]=='Y')
                                        @php
                                            $checked = 'checked';
                                        @endphp
                                    @endif
                                    <input class="form-check-input" type="checkbox" id="showCost" name="showCost" {{ $checked }}
                                        aria-label="Show Cost" style="vertical-align: middle;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="showCost" class="col-sm-3 col-form-label">Qty OH > 0</label>
                                <div class="col-sm-9">
                                    @php
                                        $oHchecked = '';
                                    @endphp
                                    @if($parameter[7]=='Y')
                                        @php
                                            $oHchecked = 'checked';
                                        @endphp
                                    @endif
                                    <input class="form-check-input" type="checkbox" id="showOhGreaterThanZero" name="showOhGreaterThanZero" {{ $oHchecked }}
                                        aria-label="Qty OH > 0" style="vertical-align: middle;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-danger px-5">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/create') }}" style="margin-bottom: 15px;">Add</a>
                <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a>
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
                                        <th>Brand Name</th>
                                        <th>Branch</th>
                                        <th style="text-align: left;">OH</th>
                                        <th style="text-align: left;">SO</th>
                                        <th style="text-align: left;">OO</th>
                                        <th style="text-align: left;">IT</th>
                                        <th style="text-align: left;">Unit</th>
                                        @if ($parameter[6]=='Y')
                                            <th>Final Cost</th>
                                            <th>AVG Cost</th>
                                        @endif
                                        <th style="text-align: left;">Final Price</th>
                                        {{-- <th style="text-align: left;">Price List</th> --}}
                                        <th>Action</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
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
                <h1 class="modal-title fs-5" id="related-info-title" style="color: white;">Related Information</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="msg-modal"></span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
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
                            '<td class="cell-style">'+objectDate.getDate()+'/'+(objectDate.getMonth()+1)+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+o[i].cust_name+'</td>'+
                            '<td class="cell-style" style="text-align:right;">'+o[i].so_qty+'</td>'+
                            '<td class="cell-style" style="text-align:right;">'+o[i].price+'</td>'+
                        '</tr>';
                    }
                }
                if (totSJ > 0) {
                    for (let i = 0; i < totSJ; i++) {
                        let objectDate = new Date(p[i].surat_jalan_date);
                        vSJ += '<tr>'+
                            '<td class="cell-style">'+p[i].surat_jalan_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+(objectDate.getMonth()+1)+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+p[i].cust_name+'</td>'+
                            '<td class="cell-style" style="text-align:right;">'+p[i].sj_qty+'</td>'+
                            '<td class="cell-style" style="text-align:right;">'+p[i].price+'</td>'+
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
                                '<td class="cell-style-header">Price (Rp)</td>'+
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
                            '<td class="cell-style">'+objectDate.getDate()+'/'+(objectDate.getMonth()+1)+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+memo[i].supplier_name+'</td>'+
                            '<td class="cell-style">'+(parseInt(memo[i].memo_qty)+parseInt(memo[i].purchase_ro_qty)-parseInt(memo[i].purchase_ro_qty_no_partial))+'</td>'+
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
                            '<td class="cell-style">'+objectDate.getDate()+'/'+(objectDate.getMonth()+1)+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+po[i].supplier_name+'</td>'+
                            '<td class="cell-style">'+(parseInt(po[i].pr_order_qty)-parseInt(po[i].purchase_ro_qty)-parseInt(po[i].purchase_ro_qty_no_partial))+'</td>'+
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
                        let objectDate = new Date(o[i].stock_transfer_date);
                        vIT += '<tr>'+
                            '<td class="cell-style">'+o[i].stock_transfer_no+'</td>'+
                            '<td class="cell-style">'+objectDate.getDate()+'/'+(objectDate.getMonth()+1)+'/'+objectDate.getFullYear()+'</td>'+
                            '<td class="cell-style">'+o[i].branch_from+'</td>'+
                            '<td class="cell-style">'+o[i].branch_to+'</td>'+
                            '<td class="cell-style">'+o[i].it_qty+'</td>'+
                        '</tr>';
                    }
                    if(vIT!==''){
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
        $('#brand_id').change(function() {
            $("#brandtype_id").empty();
            $("#brandtype_id").append(
                `<option value="">Choose...</option>`
            );
            var fd = new FormData();
            fd.append('brand_id', $('#brand_id option:selected').val());
            $.ajax({
                url: '{{ url("/disp_brand_type") }}',
                type: 'POST',
                enctype: 'application/x-www-form-urlencoded',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    let o = res[0].brand_type;
                    let totBrandType = o.length;
                    for (let i = 0; i < totBrandType; i++) {
                        optionText = o[i].brand_type;
                        optionValue = o[i].brand_id;
                        $("#brandtype_id").append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
                    }
                },
            });
        });

        $('#stock-master-list').DataTable({
            processing: true,
            ordering: false,
            // scroller: true,
            // scrollY: 500,
            searching: true,
            serverSide: true,
            ajax: {
                url: '{!! url()->current() !!}'
            },
            columns: [{ // mengambil & menampilkan kolom sesuai tabel database
                    // 0
                    data: 'part_number_with_delimiter',
                    name: 'mst_parts.part_number',
                    searchable: true,
                    orderable: false,
                },
                {
                    // 1
                    data: 'parts_name',
                    name: 'mst_parts.part_name',
                    searchable: true,
                    orderable: false,
                },
                {
                    // 2
                    data: 'part_type_name',
                    name: 'mg_01.title_ind',
                    searchable: true,
                    orderable: false,
                },
                {
                    // 3
                    data: 'brand_name',
                    name: 'mg_03.title_ind',
                    searchable: true,
                    orderable: false,
                },
                {
                    // 4
                    data: 'branch_name',
                    name: 'mb.name',
                    searchable: true,
                    orderable: false,
                },
                {
                    // 5
                    data: 'qty',
                    name: 'tx_qty_parts.qty',
                    orderable: false,
                    searchable: false,
                },
                {
                    // 6
                    data: 'SOqty',
                    name: 'SOqty',
                    orderable: false,
                    searchable: false,
                },
                {
                    // 7
                    data: 'OOqty',
                    name: 'OOqty',
                    orderable: false,
                    searchable: false,
                },
                {
                    // 8
                    data: 'ITqty',
                    name: 'ITqty',
                    orderable: false,
                    searchable: false,
                },
                {
                    // 9
                    data: 'unit_name',
                    name: 'mg_02.string_val',
                    orderable: false,
                    searchable: false,
                },
                @if ($parameter[6]=='Y')
                    {
                        data: 'final_cost_val',
                        name: 'final_cost_val',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'avg_cost',
                        name: 'mst_parts.avg_cost',
                        orderable: false,
                        searchable: false,
                    },
                @endif
                {
                    data: 'last_final_price_val',
                    name: 'last_final_price_val',
                    orderable: false,
                    searchable: false,
                },
                // {
                //     data: 'price_list',
                //     name: 'price_list',
                //     orderable: false,
                //     searchable: false,
                // },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'del_checkbox',
                    name: 'del',
                    orderable: false,
                    searchable: false,
                }
            ],
            columnDefs: [
                {
                    targets: [5,6,7,8],
                    className: 'text-right'
                },
                {
                    @if ($parameter[6]=='Y')
                        {!! 'targets: [10,11,12],' !!}
                    @else
                        {!! 'targets: [10],' !!}
                    @endif
                    render: $.fn.dataTable.render.number(',','.',0,''),
                    className: 'text-right'
                },
                {
                    @if ($parameter[6]=='Y')
                        {!! 'targets: [9,13,14],' !!}
                    @else
                        {!! 'targets: [9,11,12],' !!}
                    @endif
                    className: 'text-center'
                },
            ],
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

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection