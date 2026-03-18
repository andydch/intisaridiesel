@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="form_submit" id="form_submit" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri_folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="part_idx" id="part_idx" value="{{ $queryPart->id }}"> --}}
            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Part No</label>
                                @php
                                    $partNumber = $queryPart->part_number;
                                    if(strlen($partNumber)<11){
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                    }else{
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                    }
                                @endphp
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $partNumber }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                <div class="col-sm-9">
                                    <select class="form-select partsAjax @error('part_idx') is-invalid @enderror" id="part_idx" name="part_idx">
                                        <option value="#">Choose...</option>
                                        @php
                                            $part_idx = request()->has('part_idx') ? $request->part_idx : 0;
                                            $partList = \App\Models\Mst_part::where('active', '=', 'Y')
                                            ->when($part_idx<>0, function($q) use($part_idx) {
                                                $q->where('id','=', $part_idx);
                                            })
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
                                            <option @if($pr->id==$queryPart->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('part_idx')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="part_name" class="col-sm-3 col-form-label">Part Name</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $queryPart->part_name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="part_name" class="col-sm-3 col-form-label">Part Type</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $queryPart->part_type->title_ind }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Period</label>
                                <div class="col-sm-3">
                                    <input readonly type="text" class="form-control @error('from_date') is-invalid @enderror"
                                        maxlength="10" id="from_date" name="from_date" placeholder="Start Date"
                                        value="@if (old('from_date')){{ old('from_date') }}@else{{ (isset($request->from_date)?$request->from_date:'') }}@endif">
                                    @error('from_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="" class="col-sm-1 col-form-label">&nbsp;</label>
                                <div class="col-sm-3">
                                    <input readonly type="text" class="form-control @error('to_date') is-invalid @enderror"
                                        maxlength="10" id="to_date" name="to_date" placeholder="End Date"
                                        value="@if (old('to_date')){{ old('to_date') }}@else{{ (isset($request->to_date)?$request->to_date:'') }}@endif">
                                    @error('to_date')
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
                                            $branch_id = isset($request)?$request->branch_id:0;
                                        @endphp
                                        @foreach ($queryBranch as $p)
                                            <option @if ($branch_id==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary px-5">Generate</button>
                                    <button type="button" id="back-btn" class="btn btn-danger px-5">Back</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <table id="stock-master-list" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doc No</th>
                                        <th>Customer/Supplier</th>
                                        <th>Branch</th>
                                        <th>Price</th>
                                        <th>IN</th>
                                        <th>OUT</th>
                                        <th>OH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                        $totQty = 0;
                                        $avg_cost = 0;
                                    @endphp

                                    @isset($request)
                                        @php
                                            $from_date = explode("/",$request->from_date);
                                            $to_date = explode("/",$request->to_date);

                                            if (request()->branch_id<>''){
                                                $qAvg = \App\Models\V_log_avg_cost::where([
                                                    'part_id' => $request->part_idx,
                                                ])
                                                ->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\'')
                                                ->orderBy('updated_at','ASC')
                                                ->first();
                                                if ($qAvg){
                                                    $avg_cost = $qAvg->avg_cost;
                                                }

                                                $q = \App\Models\V_tx_qty_part::where([
                                                    'part_id' => $request->part_idx,
                                                ])
                                                ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                                                    $q->where('branch_id','=', $request->branch_id);
                                                })
                                                ->when($stockcards_part_first, function($q1) use($from_date, $stockcards_part_first){
                                                    // $q1->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                    //     'AND updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                    $q1->whereRaw('updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                })
                                                ->when(!$stockcards_part_first, function($q1) use($from_date, $to_date){
                                                    $q1->whereRaw('updated_at<\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\'')
                                                    ->where('qty','>',0);
                                                    // $q1->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                    //     'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                    // ->where('qty','>',0);
                                                })
                                                ->orderBy('updated_at','DESC')
                                                ->first();
                                                if ($q){
                                                    $totQty = $q->qty;
                                                }else{
                                                    $q2 = \App\Models\V_tx_qty_part::where([
                                                        'part_id' => $request->part_idx,
                                                    ])
                                                    ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                                                        $q->where('branch_id','=', $request->branch_id);
                                                    })
                                                    ->whereRaw('updated_at>\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                        'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                    // ->when($stockcards_part_first, function($q1) use($from_date, $stockcards_part_first){
                                                    //     $q1->whereRaw('updated_at>\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                    //         'AND updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                    //     // $q1->whereRaw('updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                    // })
                                                    // ->when(!$stockcards_part_first, function($q1) use($from_date, $to_date){
                                                    //     $q1->whereRaw('updated_at<\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                    //         'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                    //     ->where('qty','>',0);
                                                    // })
                                                    ->orderBy('updated_at','ASC')
                                                    ->first();
                                                    if ($q2){
                                                        $totQty = $q2->qty;
                                                    }
                                                }
                                            }else{
                                                // jika cabang tidak dipilih
                                                
                                                $qBranch = \App\Models\Mst_branch::where('active','Y')
                                                ->get();
                                                if ($qBranch){
                                                    foreach ($qBranch as $qB) {
                                                        $qAvg = \App\Models\V_log_avg_cost::where([
                                                            'part_id' => $request->part_idx,
                                                        ])
                                                        ->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\'')
                                                        ->orderBy('updated_at','ASC')
                                                        ->first();
                                                        if ($qAvg){
                                                            $avg_cost = $qAvg->avg_cost;
                                                        }

                                                        $q = \App\Models\V_tx_qty_part::where([
                                                            'part_id' => $request->part_idx,
                                                            'branch_id' => $qB->id,
                                                        ])
                                                        ->when($stockcards_part_first, function($q1) use($from_date, $stockcards_part_first){
                                                            // $q1->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                            //     'AND updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                            $q1->whereRaw('updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                        })
                                                        ->when(!$stockcards_part_first, function($q1) use($from_date, $to_date){
                                                            $q1->whereRaw('updated_at<\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\'')
                                                            ->where('qty','>',0);
                                                            // $q1->whereRaw('updated_at>=\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                            //     'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                            // ->where('qty','>',0);
                                                        })
                                                        ->orderBy('updated_at','DESC')
                                                        ->first();
                                                        if ($q){                                                            
                                                            $totQty += $q->qty;
                                                        }else{
                                                            $q2 = \App\Models\V_tx_qty_part::where([
                                                                'part_id' => $request->part_idx,
                                                                'branch_id' => $qB->id,
                                                            ])
                                                            ->whereRaw('updated_at>\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                                'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                            // ->when($stockcards_part_first, function($q1) use($from_date, $stockcards_part_first){
                                                            //     $q1->whereRaw('updated_at>\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                            //         'AND updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                            //     // $q1->whereRaw('updated_at<\''.$stockcards_part_first->updated_at.'\'');
                                                            // })
                                                            // ->when(!$stockcards_part_first, function($q1) use($from_date, $to_date){
                                                            //     $q1->whereRaw('updated_at<\''.$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00\' '.
                                                            //         'AND updated_at<\''.$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59\'')
                                                            //     ->where('qty','>',0);
                                                            // })
                                                            ->orderBy('updated_at','ASC')
                                                            ->first();
                                                            if ($q2){
                                                                $totQty += $q2->qty;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $request->from_date }}</td>
                                            <td>&nbsp;</td>
                                            <td>{{ 'Beginning Balance' }}</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">{{ number_format($avg_cost,0,'.',',') }}</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">{{ $totQty }}</td>
                                        </tr>
                                        @php
                                            $totQtyPerCard = $totQty;
                                        @endphp
                                    @endisset

                                    @foreach ($stockcards_part as $o)
                                        <tr>
                                            <td>
                                                {{ date_format(date_create($o->tx_date), 'd/m/Y') }}
                                                {{-- @if (strpos('doc-'.$o->doc_no,env('P_FAKTUR'))>0 || strpos('doc-'.$o->doc_no,env('P_NOTA_PENJUALAN'))>0)
                                                    {{ date_format(date_create($o->tx_date), 'd/m/Y') }}
                                                @else
                                                    {{ date_format(date_create($o->updated_at), 'd/m/Y') }}
                                                @endif --}}
                                            </td>
                                            <td>
                                                @php
                                                    $p = false;
                                                @endphp
                                                @if (substr($o->doc_no, 0, 3)==env('P_STOCK_ASSEMBLY') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_stock_assembly::where('stock_assembly_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/stock-assembly/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_STOCK_DISASSEMBLY') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_stock_disassembly::where('stock_disassembly_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/stock-disassembly/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_PURCHASE_RETUR') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_purchase_retur::where('purchase_retur_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/purchase-retur/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_FAKTUR') && !$p)
                                                    @php
                                                        $FKarr = explode("/", $o->doc_no);
                                                        $q = \App\Models\Tx_delivery_order::where('delivery_order_no','=',$FKarr[0])
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/faktur/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_NOTA_PENJUALAN') && !$p)
                                                    @php
                                                        $NParr = explode("/", $o->doc_no);
                                                        $q = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','=',$NParr[0])
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/delivery-order-local/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_NOTA_RETUR') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_nota_retur::where('nota_retur_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/nota-retur/'.urlencode($q->nota_retur_no)) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_RETUR') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_nota_retur_non_tax::where('nota_retur_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/retur/'.urlencode($q->nota_retur_no)) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_RECEIPT_ORDER') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_receipt_order::where('receipt_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/receipt-order/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_STOCK_TRANSFER') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_stock_transfer::where('stock_transfer_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/stock-transfer/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_STOCK_ADJUSTMENT') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_stock_adjustment::where('stock_adj_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/stock-adjustment/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_SALES_ORDER') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_sales_order::where('sales_order_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/sales-order/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (substr($o->doc_no, 0, 3)==env('P_SURAT_JALAN') && !$p)
                                                    @php
                                                        $q = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$o->doc_no)
                                                        ->first();
                                                    @endphp
                                                    @if($q)
                                                        <a href="{{ url('/tx/surat-jalan/'.$q->id) }}" target="_new" style="text-decoration: underline;">{{ $o->doc_no }}</a>
                                                        @php
                                                            $p = true;
                                                        @endphp
                                                    @endif
                                                @endif
                                                @if (!$p)
                                                    {{ substr($o->doc_no, 0, 3)==env('P_FAKTUR')?'ada':'tidak ada' }}
                                                    {{-- {{ $o->doc_no }} --}}
                                                @endif
                                            </td>
                                            <td>{{ $o->customer_or_supplier }}</td>
                                            <td>{{ $o->branch_name }}</td>
                                            <td style="text-align: right;">{{ number_format($o->avg_cost,0,'.',',') }}</td>
                                            <td style="text-align: right;">
                                                @if ($o->status=='IN')
                                                    {{ $o->qty }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($o->status=='OUT')
                                                    {{ $o->qty }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($o->status=='IN')
                                                    @php
                                                        $totQtyPerCard = $totQtyPerCard+$o->qty;
                                                    @endphp
                                                @endif
                                                @if ($o->status=='OUT' && (substr($o->doc_no, 0, 3)!=env('P_SALES_ORDER') || substr($o->doc_no, 0, 3)==env('P_SURAT_JALAN')))
                                                    @php
                                                        $totQtyPerCard = $totQtyPerCard-$o->qty;
                                                    @endphp
                                                @endif
                                                {{ $totQtyPerCard }}
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doc No</th>
                                        <th>Customer/Supplier</th>
                                        <th>Branch</th>
                                        <th>Price</th>
                                        <th>IN</th>
                                        <th>OUT</th>
                                        <th>OH</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#stock-master-list').DataTable({
            'ordering': false,
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        // old format : YYYY-MM-DD
        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#from_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#to_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#back-btn").click(function() {
            location.href='{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });

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
    });
</script>
@endsection
