@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
{{-- <link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}"> --}}
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
        @include(ENV('REPORT_FOLDER_NAME').'.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/master-inventory') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('branch_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
                                                @endphp
                                                @foreach ($branches as $branch)
                                                    <option @if($p_Id==$branch->id){{ 'selected' }}@endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="brand_id" class="col-sm-3 col-form-label">Brand</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('brand_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('brand_id')?old('brand_id'):(isset($reqs)?$reqs->brand_id:0));
                                                @endphp
                                                @foreach ($brands as $brand)
                                                    <option @if($p_Id==$brand->id){{ 'selected' }}@endif value="{{ $brand->id }}">{{ $brand->title_ind }}</option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="brand_id" class="col-sm-3 col-form-label">OH=0?</label>
                                        <div class="col-sm-9" style="vertical-align: middle;display: inherit;">
                                            <input type="checkbox" name="oh_is_zero" id="oh_is_zero" @if (old('oh_is_zero')=='on' || (isset($reqs)?$reqs->oh_is_zero:'')=='on'){{ 'checked' }}@endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        {{-- <div class="col-sm-3">&nbsp;</div> --}}
                                        <div class="col-sm-12">
                                            {{-- <input type="button" id="generate-report" class="btn btn-primary px-5" value="Generate"> --}}
                                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="display: none;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="master-inventory" style="width:100%;">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="text-align: center;">PARTS NO</th>
                                    <th rowspan="2" style="text-align: center;">PARTS NAME</th>
                                    <th rowspan="2" style="text-align: center;">PARTS TYPE</th>
                                    <th rowspan="2" style="text-align: center;">BRAND</th>
                                    <th rowspan="2" style="text-align: center;">BRAND TYPE</th>
                                    <th rowspan="2" style="text-align: center;">COST AVG ({{ $qCurrency->string_val }})</th>
                                    <th colspan="4" style="text-align: center;">QTY</th>
                                    <th rowspan="2" style="text-align: center;">FINAL PRICE ({{ $qCurrency->string_val }})</th>
                                    <th rowspan="2" style="text-align: center;">TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                                </tr>
                                <tr>
                                    <th style="text-align: center;">OH</th>
                                    <th style="text-align: center;">SO</th>
                                    <th style="text-align: center;">OO</th>
                                    <th style="text-align: center;">IT</th>
                                </tr>
                            </thead>
                            @isset($reqs)
                            <tbody>
                                @php
                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->orderBy('name','ASC')
                                    ->get();

                                    $grandtotal = 0;
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <th>{{ strtoupper($branch->name) }}</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    @php
                                        $brands = \App\Models\Mst_global::where([
                                            'data_cat' => 'brand',
                                            'active' => 'Y'
                                        ])
                                        ->when($reqs->brand_id!='0', function($q) use($reqs) {
                                            $q->where('id','=',$reqs->brand_id);
                                        })
                                        ->orderBy('title_ind', 'ASC')
                                        ->get();

                                        $totalPerBranch = 0;
                                    @endphp
                                    @foreach ($brands as $brand)
                                        @php
                                            $rpts = \App\Models\Tx_qty_part::leftJoin('mst_parts as pr','tx_qty_parts.part_id','=','pr.id')
                                            ->leftJoin('mst_branches as br','tx_qty_parts.branch_id','=','br.id')
                                            ->leftJoin('mst_globals as bd','pr.brand_id','=','bd.id')
                                            ->leftJoin('mst_globals as pr_type','pr.part_type_id','=','pr_type.id')
                                            ->leftJoin('mst_part_brand_types as pbd_type','pr.id','=','pbd_type.part_id')
                                            ->leftJoin('mst_brand_types as bd_type','pbd_type.brand_type_id','=','bd_type.id')
                                            ->select(
                                                'br.name as branch_name',
                                                'bd.title_ind as brand_name',
                                                'bd.id as bd_id',
                                                'pr.part_number',
                                                'pr.part_name',
                                                'pr.avg_cost',
                                                'pr_type.title_ind as part_type_name',
                                                'bd_type.brand_type as brand_type_name',
                                                'tx_qty_parts.qty as qty_per_branch',
                                                'tx_qty_parts.part_id as qty_part_id',
                                                'tx_qty_parts.branch_id',
                                                'tx_qty_parts.id as qty_id',
                                            )
                                            // sales order
                                            ->addSelect(['sales_order_qty' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0)')
                                                ->leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                                ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                                ->whereNotIn('txso.id',function (\Illuminate\Database\Query\Builder $query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                    ->where('tx_do_parts.active','=','Y')
                                                    ->where('tx_do.active','=','Y');
                                                })
                                                ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                                ->where('tx_sales_order_parts.active','=','Y')
                                                ->where('txso.sales_order_no','NOT LIKE','%Draft%')
                                                ->where('txso.need_approval','=','N')
                                                ->where('txso.active','=','Y')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty_parts.branch_id)')
                                            ])
                                            // surat jalan
                                            ->addSelect(['surat_jalan_qty' => \App\Models\Tx_surat_jalan_part::selectRaw('IFNULL(SUM(tx_surat_jalan_parts.qty),0)')
                                                ->leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
                                                ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
                                                ->whereNotIn('txsj.id',function (\Illuminate\Database\Query\Builder $query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                    ->where('tx_do_parts.active','=','Y')
                                                    ->where('tx_do.active','=','Y');
                                                })
                                                ->whereColumn('tx_surat_jalan_parts.part_id','tx_qty_parts.part_id')
                                                ->where('tx_surat_jalan_parts.active','=','Y')
                                                ->where('txsj.surat_jalan_no','NOT LIKE','%Draft%')
                                                ->where('txsj.need_approval','=','N')
                                                ->where('txsj.active','=','Y')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txsj.branch_id IS null) OR txsj.branch_id=tx_qty_parts.branch_id)')
                                            ])
                                            // purchase memo
                                            ->addSelect(['purchase_memo_qty' => \App\Models\Tx_purchase_memo_part::selectRaw('IFNULL(SUM(tx_purchase_memo_parts.qty),0)')    // total qty dari memo yg aktif
                                                ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                                                ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                                ->whereColumn('tx_purchase_memo_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('tx_memo.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->where('tx_purchase_memo_parts.active','=','Y')
                                                ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                                ->where('tx_memo.active','=','Y')
                                            ])
                                            // purchase order
                                            ->addSelect(['purchase_order_qty' => \App\Models\Tx_purchase_order_part::selectRaw('IFNULL(SUM(tx_purchase_order_parts.qty),0)')  // total qty dari po yg aktif
                                                ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                                                ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                                ->whereColumn('tx_purchase_order_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('tx_order.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->where('tx_purchase_order_parts.active','=','Y')
                                                ->where('tx_order.approved_by','<>',null)
                                                ->where('tx_order.active','=','Y')
                                            ])
                                            // receipt order - partial
                                            ->addSelect(['purchase_ro_qty_mo' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg approved
                                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->whereIn('tx_receipt_order_parts.po_mo_no', function(\Illuminate\Database\Query\Builder $query){
                                                    $query->select('tx_memo.memo_no')
                                                    ->from('tx_purchase_memos as tx_memo')
                                                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                                    ->where('tx_memo.active','=','Y');
                                                })
                                            ])
                                            ->addSelect(['purchase_ro_qty_po' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg approved
                                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->whereIn('tx_receipt_order_parts.po_mo_no', function(\Illuminate\Database\Query\Builder $query){
                                                    $query->select('tx_order.purchase_no')
                                                    ->from('tx_purchase_orders as tx_order')
                                                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                                    ->where('tx_order.approved_by','<>',null)
                                                    ->where('tx_order.active','=','Y');
                                                })
                                            ])
                                            // receipt order - non partial
                                            ->addSelect(['purchase_ro_qty_no_partial_mo' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->whereIn('tx_receipt_order_parts.po_mo_no', function(\Illuminate\Database\Query\Builder $query){
                                                    $query->select('tx_memo.memo_no')
                                                    ->from('tx_purchase_memos as tx_memo')
                                                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                                    ->where('tx_memo.active','=','Y');
                                                })
                                            ])
                                            ->addSelect(['purchase_ro_qty_no_partial_po' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                                // ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->whereIn('tx_receipt_order_parts.po_mo_no', function(\Illuminate\Database\Query\Builder $query){
                                                    $query->select('tx_order.purchase_no')
                                                    ->from('tx_purchase_orders as tx_order')
                                                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                                    ->where('tx_order.approved_by','<>',null)
                                                    ->where('tx_order.active','=','Y');
                                                })
                                            ])
                                            // in transit
                                            ->addSelect(['in_transit_qty' => \App\Models\Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                                                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                                                ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
                                                ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
                                                ->where('tx_stock_transfer_parts.active','=','Y')
                                                ->where('tx_stock.approved_by','<>',null)
                                                ->where('tx_stock.received_by','=',null)
                                                ->where('tx_stock.active','=','Y')
                                            ])
                                            // final price terbaru
                                            ->addSelect(['last_final_price' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(tx_sales_order_parts.price,0)')
                                                ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                                                ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                                ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                                // ---
                                                // gunakan kode cabang user ketika cabang SO kosong
                                                // jika cabang SO ada maka gunakan kode cabang SO
                                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty_parts.branch_id)')
                                                // ---
                                                ->where('tx_sales_order_parts.active','=','Y')
                                                ->where('txso.active','=','Y')
                                                ->orderBy('txso.created_at','DESC')     // ambil harga terbaru dari
                                                ->limit(1)                              // data di baris pertama
                                            ])
                                            // ->whereRaw('tx_qty_parts.qty>0')
                                            ->when($reqs->oh_is_zero!='on', function($q) use($reqs) {
                                                $q->whereRaw('tx_qty_parts.qty>0');
                                            })
                                            ->whereRaw('tx_qty_parts.branch_id='.$branch->id)
                                            ->whereRaw('pr.brand_id='.$brand->id)
                                            ->whereRaw('pr.active=\'Y\'')
                                            // ->whereRaw('pbd_type.active=\'Y\'')
                                            ->orderBy('br.name','ASC')
                                            ->orderBy('bd.title_ind','ASC')
                                            ->orderBy('pr.part_number','ASC')
                                            ->get();

                                            $totalPerBrand = 0;
                                        @endphp
                                        @foreach ($rpts as $rpt)
                                            @php
                                                $totSO = $rpt->sales_order_qty+$rpt->surat_jalan_qty;
                                                $totOO = ($rpt->purchase_memo_qty+$rpt->purchase_order_qty)-($rpt->purchase_ro_qty_mo+$rpt->purchase_ro_qty_po+$rpt->purchase_ro_qty_no_partial_mo+$rpt->purchase_ro_qty_no_partial_po);
                                            @endphp
                                            @if ($reqs->oh_is_zero=='on')
                                                @if ($rpt->qty_per_branch>0 || ($rpt->qty_per_branch==0 && ($totSO>0 || $totOO>0 || $rpt->in_transit_qty>0)))
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $partNumber = $rpt->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            @endphp
                                                            {{ $partNumber }}
                                                        </td>
                                                        <td>{{ $rpt->part_name }}</td>
                                                        <td>{{ $rpt->part_type_name }}</td>
                                                        <td>{{ $rpt->brand_name }}</td>
                                                        <td>{{ $rpt->brand_type_name }}</td>
                                                        <td style="text-align: right;">{{ number_format($rpt->avg_cost,0,'.',',') }}</td>
                                                        <td style="text-align: right;">{{ $rpt->qty_per_branch }}</td>
                                                        <td style="text-align: right;">{{ $totSO }}</td>
                                                        <td style="text-align: right;">{{ $totOO }}</td>
                                                        <td style="text-align: right;">{{ $rpt->in_transit_qty }}</td>
                                                        <td style="text-align: right;">{{ number_format($rpt->last_final_price,0,'.',',') }}</td>
                                                        <td style="text-align: right;">{{ number_format(($rpt->qty_per_branch*round($rpt->avg_cost))+($rpt->in_transit_qty*round($rpt->avg_cost)),0,'.',',') }}</td>
                                                    </tr>
                                                    @php
                                                        $totalPerBrand += ($rpt->qty_per_branch*round($rpt->avg_cost))+($rpt->in_transit_qty*round($rpt->avg_cost));
                                                    @endphp
                                                @endif
                                            @else
                                                <tr>
                                                    <td>
                                                        @php
                                                            $partNumber = $rpt->part_number;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        {{ $partNumber }}
                                                    </td>
                                                    <td>{{ $rpt->part_name }}</td>
                                                    <td>{{ $rpt->part_type_name }}</td>
                                                    <td>{{ $rpt->brand_name }}</td>
                                                    <td>{{ $rpt->brand_type_name }}</td>
                                                    <td style="text-align: right;">{{ number_format($rpt->avg_cost,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ $rpt->qty_per_branch }}</td>
                                                    <td style="text-align: right;">{{ $totSO }}</td>
                                                    <td style="text-align: right;">{{ $totOO }}</td>
                                                    <td style="text-align: right;">{{ $rpt->in_transit_qty }}</td>
                                                    <td style="text-align: right;">{{ number_format($rpt->last_final_price,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format(($rpt->qty_per_branch*round($rpt->avg_cost))+($rpt->in_transit_qty*round($rpt->avg_cost)),0,'.',',') }}</td>
                                                </tr>
                                                @php
                                                    $totalPerBrand += ($rpt->qty_per_branch*round($rpt->avg_cost))+($rpt->in_transit_qty*round($rpt->avg_cost));
                                                @endphp
                                            @endif
                                        @endforeach
                                        @if ($totalPerBrand>0)
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <th style="text-align: right;">{{ number_format($totalPerBrand,0,'.',',') }}</th>
                                            </tr>
                                        @endif
                                        @php
                                            $totalPerBranch += $totalPerBrand;
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <th>TOTAL</th>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <th style="text-align: right;">{{ number_format($totalPerBranch,0,'.',',') }}</th>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <th>&nbsp;</th>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $grandtotal += $totalPerBranch;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td>&nbsp;</td>
                                    <th>GRAND TOTAL</th>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <th style="text-align: right;">{{ number_format($grandtotal,0,'.',',') }}</th>
                                </tr>
                            </tbody>
                            @endisset
                        </table>
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
{{-- datetime picker --}}
{{-- <script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script> --}}

{{-- datatables --}}
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

{{-- select & search --}}
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#master-inventory").DataTable({
            'ordering': false,
        });

        $("#generate-report").click(function() {
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function() {
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
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
