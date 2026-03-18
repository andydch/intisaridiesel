@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
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
        <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/outstanding-purchase-order-per-pn') }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Year</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $p_Id = (old('year_id')?old('year_id'):(isset($reqs)?$reqs->year_id:0));
                                                @endphp
                                                @for ($y=2023;$y<=date_format(now(),'Y');$y++)
                                                    <option @if ($p_Id==$y) {{ 'selected' }} @endif value="{{ $y }}">{{ $y }}</option>
                                                @endfor
                                            </select>
                                            @error('year_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <input type="button" id="generate-report" class="btn btn-light px-5" value="Generate">
                            <input type="button" id="download-report" class="btn btn-light px-5" value="Download Report">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="out-po-per-partno" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>PARTS NAME</th>
                                    <th>PARTS TYPE</th>
                                    <th>NAMA SUPPLIER</th>
                                    <th>NO PO/MO</th>
                                    <th>DATE</th>
                                    <th>QTY ORD</th>
                                    <th>HARGA DPP ({{ $qCurrency->string_val }})</th>
                                    <th>TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th>QTY OH</th>
                                    <th>ESTIMASI SUPPLY</th>
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
                                </tr>
                                @php
                                    $subtotal = 0;

                                    // purchase order
                                    $po_parts = \App\Models\Tx_purchase_order_part::leftJoin('tx_purchase_orders as tx_po','tx_purchase_order_parts.order_id','=','tx_po.id')
                                    ->leftJoin('mst_parts as msp','tx_purchase_order_parts.part_id','=','msp.id')
                                    ->leftJoin('mst_suppliers as ms_sup','tx_po.supplier_id','=','ms_sup.id')
                                    ->leftJoin('mst_globals as pr_type','msp.part_type_id','=','pr_type.id')
                                    ->select(
                                        'msp.part_number',
                                        'msp.part_name',
                                        'pr_type.title_ind as part_type_name',
                                        'ms_sup.name as supplier_name',
                                        'tx_po.purchase_no',
                                        'tx_po.purchase_date',
                                        'tx_purchase_order_parts.qty as qty_ord',
                                        'tx_purchase_order_parts.price as price_dpp',
                                        'tx_po.est_supply_date',
                                    )
                                    ->addSelect([
                                        'qty_ro' => \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                        ->selectRaw('SUM(tx_receipt_order_parts.qty)')
                                        ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                        ->whereRaw('tx_receipt_order_parts.po_mo_no=tx_po.purchase_no AND tx_receipt_order_parts.part_id=tx_purchase_order_parts.id')
                                        ->where([
                                            'tx_receipt_order_parts.active' => 'Y',
                                            'txro.active' => 'Y',
                                        ])
                                    ])
                                    ->addSelect([
                                        'qty_now' => \App\Models\Tx_qty_part::select('qty')
                                        ->whereRaw('tx_qty_parts.part_id=tx_purchase_order_parts.part_id AND tx_qty_parts.branch_id=tx_po.branch_id')
                                    ])
                                    ->whereNotIn('order_id', function($q){
                                        $q->select('po_mo_id')
                                        ->from('tx_receipt_order_parts')
                                        ->leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                        ->whereColumn('po_mo_no','tx_po.purchase_no')
                                        ->whereColumn('part_id','tx_purchase_order_parts.part_id')
                                        ->where([
                                            'tx_receipt_order_parts.is_partial_received' => 'N',
                                            'tx_receipt_order_parts.active' => 'Y',
                                        ]);
                                    })
                                    ->where([
                                        'tx_purchase_order_parts.active' => 'Y',
                                        'tx_po.branch_id' => $branch->id,
                                        'tx_po.active' => 'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($po_parts as $part)
                                    @if (($part->qty_ord-$part->qty_ro)>0)
                                        <tr>
                                            <td>
                                                @php
                                                    $partNumber = $part->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                {{ strtoupper($partNumber) }}
                                            </td>
                                            <td>{{ strtoupper($part->part_name) }}</td>
                                            <td>{{ $part->part_type_name }}</td>
                                            <td>{{ strtoupper($part->supplier_name) }}</td>
                                            <td>{{ $part->purchase_no }}</td>
                                            <td>{{ date_format(date_create($part->purchase_date),"d/m/Y") }}</td>
                                            <td style="text-align: right;">{{ $part->qty_ord-$part->qty_ro }}</td>
                                            <td style="text-align: right;">{{ number_format($part->price_dpp,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format(($part->price_dpp*$part->qty_ord),0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ $part->qty_now }}</td>
                                            <td>{{ (!is_null($part->est_supply_date)?date_format(date_create($part->est_supply_date),"d/m/Y"):'') }}</td>
                                        </tr>
                                        @php
                                            $subtotal+=($part->price_dpp*$part->qty_ord);
                                        @endphp
                                    @endif
                                @endforeach

                                @php
                                    // purchase memo
                                    $mo_parts = \App\Models\Tx_purchase_memo_part::leftJoin('tx_purchase_memos as tx_mo','tx_purchase_memo_parts.memo_id','=','tx_mo.id')
                                    ->leftJoin('mst_parts as msp','tx_purchase_memo_parts.part_id','=','msp.id')
                                    ->leftJoin('mst_suppliers as ms_sup','tx_mo.supplier_id','=','ms_sup.id')
                                    ->leftJoin('mst_globals as pr_type','msp.part_type_id','=','pr_type.id')
                                    ->select(
                                        'msp.part_number',
                                        'msp.part_name',
                                        'pr_type.title_ind as part_type_name',
                                        'ms_sup.name as supplier_name',
                                        'tx_mo.memo_no',
                                        'tx_mo.memo_date',
                                        'tx_purchase_memo_parts.qty as qty_ord',
                                        'tx_purchase_memo_parts.price as price_dpp',
                                    )
                                    ->addSelect([
                                        'qty_ro' => \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                        ->selectRaw('SUM(tx_receipt_order_parts.qty)')
                                        ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                        ->whereRaw('tx_receipt_order_parts.po_mo_no=tx_mo.memo_no AND tx_receipt_order_parts.part_id=tx_purchase_memo_parts.part_id')
                                        ->where([
                                            'tx_receipt_order_parts.active' => 'Y',
                                            'txro.active' => 'Y',
                                        ])
                                    ])
                                    ->addSelect([
                                        'qty_now' => \App\Models\Tx_qty_part::select('qty')
                                        ->whereRaw('tx_qty_parts.part_id=tx_purchase_memo_parts.part_id AND tx_qty_parts.branch_id=tx_mo.branch_id')
                                    ])
                                    ->whereNotIn('memo_id', function($q){
                                        $q->select('po_mo_id')
                                        ->from('tx_receipt_order_parts')
                                        ->leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                        ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                        ->whereColumn('po_mo_no','tx_mo.memo_no')
                                        ->whereColumn('part_id','tx_purchase_memo_parts.part_id')
                                        ->where([
                                            'tx_receipt_order_parts.is_partial_received' => 'N',
                                            'tx_receipt_order_parts.active' => 'Y',
                                        ]);
                                    })
                                    ->where([
                                        'tx_purchase_memo_parts.active' => 'Y',
                                        'tx_mo.branch_id' => $branch->id,
                                        'tx_mo.active' => 'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($mo_parts as $part)
                                    @if (($part->qty_ord-$part->qty_ro)>0)
                                        <tr>
                                            <td>
                                                @php
                                                    $partNumber = $part->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                {{ strtoupper($partNumber) }}
                                            </td>
                                            <td>{{ strtoupper($part->part_name) }}</td>
                                            <td>{{ $part->part_type_name }}</td>
                                            <td>{{ strtoupper($part->supplier_name) }}</td>
                                            <td>{{ $part->memo_no }}</td>
                                            <td>{{ date_format(date_create($part->memo_date),"d/m/Y") }}</td>
                                            <td style="text-align: right;">{{ $part->qty_ord-$part->qty_ro }}</td>
                                            <td style="text-align: right;">{{ number_format($part->price_dpp,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format(($part->price_dpp*$part->qty_ord),0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ $part->qty_now }}</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        @php
                                            $subtotal+=($part->price_dpp*$part->qty_ord);
                                        @endphp
                                    @endif
                                @endforeach
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>TOTAL</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th style="text-align: right;">{{ number_format($subtotal,0,'.',',') }}</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                                @php
                                    $grandtotal+=$subtotal;
                                @endphp
                            @endforeach
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>GRAND TOTAL</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th style="text-align: right;">{{ number_format($grandtotal,0,'.',',') }}</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </tbody>
                            @endisset
                            <tfoot>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>PARTS NAME</th>
                                    <th>PARTS TYPE</th>
                                    <th>NAMA SUPPLIER</th>
                                    <th>NO PO/MO</th>
                                    <th>DATE</th>
                                    <th>QTY ORD</th>
                                    <th>HARGA DPP ({{ $qCurrency->string_val }})</th>
                                    <th>TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th>QTY OH</th>
                                    <th>ESTIMASI SUPPLY</th>
                                </tr>
                            </tfoot>
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
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#out-po-per-partno").DataTable({
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
