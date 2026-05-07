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
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$orders->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                            @if(session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Order No</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="order_no" id="order_no" class="@error('order_no') is-invalid @enderror" value="{{ $orders->purchase_no }}">
                                    <input type="hidden" name="order_no_tmp" class="@error('order_no_tmp') is-invalid @enderror">
                                    <label for="" class="col-form-label part-id">{{ $orders->purchase_no }}</label>
                                    @error('order_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('order_no_tmp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="order_date" class="col-sm-3 col-form-label">Order Date</label>
                                <label for="order_date" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->purchase_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $supplierId = (old('supplier_id')?old('supplier_id'):$orders->supplier_id);
                                        @endphp
                                        @foreach ($suppliers as $p)
                                            <option @if($supplierId==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ (!is_null($p->entity_type)?$p->entity_type->title_ind:'').' '.$p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="supplier_type_id" id="supplier_type_id"
                                value="@if(old('supplier_type_id')){{ old('supplier_type_id') }}@else{{ $orders->supplier_type_id }}@endif">
                            <div id="supplier_data" class="row mb-3">
                                <label for="supplier_data" class="col-sm-3 col-form-label">Information</label>
                                <div id="supplier_info" class="col-sm-9">
                                    @if(count($supplierPics)>0)
                                        {!!
                                        (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind:'').' '.$supplierPics[0]->name.
                                        '<br />Address: '.$supplierPics[0]->office_address.
                                        (($supplierPics[0]->subdistrict?$supplierPics[0]->subdistrict->sub_district_name:'')=='Other'?'':
                                        ', '.ucwords(strtolower(($supplierPics[0]->subdistrict?$supplierPics[0]->subdistrict->sub_district_name:'')))).
                                        (($supplierPics[0]->district?$supplierPics[0]->district->district_name:'')=='Other'?'':
                                        ', '.($supplierPics[0]->district?$supplierPics[0]->district->district_name:'')).
                                        (($supplierPics[0]->city?$supplierPics[0]->city->city_name:'')=='Other'?'':
                                        (($supplierPics[0]->city?'<br />'.$supplierPics[0]->city->city_type:'')=='Luar Negeri'?'':($supplierPics[0]->city?$supplierPics[0]->city->city_type:'')).' '.
                                        ($supplierPics[0]->city?$supplierPics[0]->city->city_name:'')).
                                        (($supplierPics[0]->province?$supplierPics[0]->province->province_name:'')=='Other'?'':
                                        ($supplierPics[0]->province?'<br />'.$supplierPics[0]->province->province_name:'')).
                                        '<br />'.$supplierPics[0]->country->country_name.
                                        ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                        ' '.$supplierPics[0]->subdistrict->post_code)
                                        !!}
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="quotation_id" class="col-sm-3 col-form-label">PQ No</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('quotation_id') is-invalid @enderror" id="quotation_id" name="quotation_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $quotationId = (old('quotation_id')?old('quotation_id'):$orders->quotation_id);
                                        @endphp
                                        @foreach ($quotations as $p)
                                            <option @if($quotationId==$p->pq_id){{ 'selected' }}@endif value="{{ $p->pq_id }}">{{ $p->quotation_no }}</option>
                                        @endforeach
                                    </select>
                                    @error('quotation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="supplier_pic" class="col-sm-3 col-form-label">PIC*</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('supplier_pic') is-invalid @enderror" id="supplier_pic" name="supplier_pic">
                                        <option value="#">Choose...</option>
                                        @php
                                        $supplierPic = (old('supplier_pic')?old('supplier_pic'):$orders->pic_idx);
                                        @endphp
                                        @foreach ($supplierPics as $p)
                                        <option @if($supplierPic==1) {{ 'selected' }} @endif value="1">
                                            {{ $p->pic1_name }}
                                        </option>
                                        <option @if($supplierPic==2) {{ 'selected' }} @endif value="2">
                                            {{ $p->pic2_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_pic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="currency_id" class="col-sm-3 col-form-label">Currency*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('currency_id') is-invalid @enderror" id="currency_id" name="currency_id">
                                        <option value="#">Choose...</option>
                                        @php
                                        $currencyId = (old('currency_id')?old('currency_id'):$orders->currency_id);
                                        @endphp
                                        @foreach ($currency as $b)
                                        <option @if($currencyId==$b->currency_id) {{ 'selected' }} @endif
                                            value="{{ $b->currency_id }}">{{ $b->currency->title_ind }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if($userLogin->is_director=='Y')
                                <input type="hidden" name="is_director" id="is_director" value="Y">
                                <div class="row mb-3">
                                    <label for="branch_id" class="col-sm-3 col-form-label">Ship To*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $branchId = (old('branch_id')?old('branch_id'):$orders->branch_id);
                                            @endphp
                                            @foreach ($branches as $b)
                                                <option @if($branchId==$b->id){{ 'selected' }}@endif value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="is_director" id="is_director" value="N">
                                <input type="hidden" name="branch_id" id="branch_id" value="{{ $userLogin->branch_id }}">
                            @endif
                            <div class="row mb-3">
                                <label for="courier_id" class="col-sm-3 col-form-label">Ship By</label>
                                <div class="col-sm-3">
                                    <select class="form-select" name="courier_type" id="courier_type">
                                        <option @if(old('courier_type')==env('AMBIL_SENDIRI') || $orders->courier_type==env('AMBIL_SENDIRI')){{ 'selected' }}@endif value="{{ env('AMBIL_SENDIRI') }}">{{ env('AMBIL_SENDIRI_STR') }}</option>
                                        <option @if(old('courier_type')==env('DIANTAR') || $orders->courier_type==env('DIANTAR')){{ 'selected' }}@endif value="{{ env('DIANTAR') }}">{{ env('DIANTAR_STR') }}</option>
                                        <option @if(old('courier_type')==env('COURIER') || $orders->courier_type==env('COURIER')){{ 'selected' }}@endif value="{{ env('COURIER') }}">{{ env('COURIER_STR') }}</option>
                                    </select>
                                </div>
                                <div id="courier-list" class="col-sm-6" style="@if(old('courier_type')==env('COURIER') || $orders->courier_type==env('COURIER')){{ 'display: block;' }}@else{{ 'display: none;' }}@endif">
                                    <select class="form-select single-select @error('courier_id') is-invalid @enderror" id="courier_id" name="courier_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $courierId = (old('courier_id')?old('courier_id'):$orders->courier_id);
                                        @endphp
                                        @foreach ($couriers as $p)
                                        <option @if($courierId==$p->id) {{ 'selected' }} @endif
                                            value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('courier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="est_supply_date" class="col-sm-3 col-form-label">Estimated Supply</label>
                                <div class="col-sm-9">
                                    <input readonly type="text" class="form-control @error('est_supply_date') is-invalid @enderror" maxlength="10"
                                        id="est_supply_date" name="est_supply_date" placeholder="Estimated Supply Date"
                                        value="@if(old('est_supply_date')){{ old('est_supply_date') }}@else{{ !is_null($orders->est_supply_date)?date_format(date_create($orders->est_supply_date), 'd/m/Y'):'' }}@endif">
                                    @error('est_supply_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">VAT</label>
                                <div class="col-sm-9">
                                    @php
                                        $vat = $orders->is_vat;
                                    @endphp
                                    @if(old('vat'))
                                        @if(old('vat') == 'on')
                                            @php
                                                $vat = 'Y';
                                            @endphp
                                        @else
                                            @php
                                                $vat = 'N';
                                            @endphp
                                        @endif
                                    @endif
                                    <input class="form-check-input" type="checkbox" id="vat" name="vat" aria-label="VAT" @if($vat=='Y' ){{ 'checked' }}@endif>
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
                                        <th scope="col" style="width: 2%;text-align:center;">#</th>
                                        <th scope="col" style="width: 15%;">Part</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        @php
                                            $price_rp = 'Price ('.$qCurrency->string_val.')';
                                            $price_rp_total = 'Total ('.$qCurrency->string_val.')';
                                        @endphp
                                        @if(old('supplier_type_id'))
                                            @if(old('supplier_type_id')==10)
                                                @php
                                                    $price_rp = 'Price';
                                                    $price_rp_total = 'Total';
                                                @endphp
                                            @endif
                                        @else
                                            @if($orders->supplier_type_id==10)
                                                @php
                                                    $price_rp = 'Price';
                                                    $price_rp_total = 'Total';
                                                @endphp
                                            @endif
                                        @endif
                                        <th id="price-rp" scope="col" style="width: 20%;">{{ $price_rp }}</th>
                                        <th id="price-rp-total" style="width: 10%;">{{ $price_rp_total }}</th>
                                        <th scope="col" style="width: 15%;">Description</th>
                                        <th scope="col" style="width: 10%;">Final&nbsp;Cost/FOB</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">OO</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $iRowPart = 1;
                                    @endphp
                                    @if(old('totalRow'))
                                        @for ($i = 0; $i < $totRow; $i++)
                                            @if(old('part_id'.$i))
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="purchase_order_row_number{{ $i }}" class="col-form-label">{{ $iRowPart }}.</label>
                                                        <input type="hidden" name="order_part_id_{{ $i }}" id="order_part_id_{{ $i }}"
                                                            value="@if(old('order_part_id_'.$i)){{ old('order_part_id_'.$i) }}@endif">
                                                    </th>
                                                    @php
                                                        // tampilkan QTY yg sudah masuk RO dan berstatus active
                                                        $sumQtyRO = \App\Models\Tx_receipt_order_part::whereIn('receipt_order_id', function($q) {
                                                            $q->select('id')
                                                            ->from('tx_receipt_orders')
                                                            ->where('active', 'Y');
                                                        })
                                                        ->where('po_mo_no', $orders->purchase_no)
                                                        ->where('po_mo_id', old('order_part_id_'.$i))
                                                        ->where('part_id', old('part_id'.$i) ? old('part_id'.$i) : 0)
                                                        ->where('active', 'Y')
                                                        ->sum('qty');

                                                        $is_partial_received = 'Y';
                                                        $is_part_in_RO = $sumQtyRO>0?'Y':'N';
                                                        $qIsPartialReceived = \App\Models\Tx_receipt_order_part::select('is_partial_received')
                                                        ->whereIn('receipt_order_id', function($q) {
                                                            $q->select('id')
                                                            ->from('tx_receipt_orders')
                                                            ->where('active', 'Y');
                                                        })
                                                        ->where('po_mo_no', $orders->purchase_no)
                                                        ->where('po_mo_id', old('order_part_id_'.$i))
                                                        ->where('part_id', old('part_id'.$i) ? old('part_id'.$i) : 0)
                                                        ->where('is_partial_received', 'N') // memastikan jika ada status partial received==N
                                                        ->where('active', 'Y')
                                                        ->first();
                                                        if ($qIsPartialReceived){
                                                            $is_partial_received = $qIsPartialReceived->is_partial_received;
                                                        }

                                                        $q = \App\Models\Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                                                        ->leftJoin('mst_globals as q_type', 'mst_parts.quantity_type_id', '=', 'q_type.id')
                                                        ->select(
                                                            'mst_parts.*',
                                                            'tx_qty_parts.qty as total_qty',
                                                            'q_type.title_ind AS quantity_type',
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
                                                        @php
                                                            $partId = old('part_id'.$i) ? old('part_id'.$i) : 0;
                                                            $partName = '';
                                                            $partNumber = '';
                                                        @endphp
                                                        @if ($is_partial_received=='N' || $is_part_in_RO=='Y')
                                                            @php
                                                                $partOne = \App\Models\Mst_part::where([
                                                                    'id' => $partId,
                                                                ])
                                                                ->first();
                                                                if ($partOne){
                                                                    $partName = $partOne->part_name;
                                                                    $partNumber = $partOne->part_number;
                                                                    if(strlen($partNumber)<11){
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                    }else{
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                    }
                                                                }
                                                            @endphp
                                                            <input type="hidden" id="part_id{{ $i }}" name="part_id{{ $i }}" value="{{ $partId }}">
                                                            <label id="part_id{{ $i }}-lbl" for="" class="col-form-label">{{ $partNumber .' : '.$partName }}</label>
                                                        @else
                                                            <select class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                                id="part_id{{ $i }}" name="part_id{{ $i }}" onchange="dispPriceRef(this.value, {{ $i }});">
                                                                <option value="#">Choose...</option>
                                                                @php
                                                                    $partList = \App\Models\Mst_part::where([
                                                                        'id' => $partId,
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
                                                                    <option @if($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('part_id'.$i)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input @if($is_partial_received=='N'){!! 'readonly' !!}@endif onchange="totalPrice({{ $i }});" type="text" 
                                                            style="text-align: right;{{ $is_partial_received=='N'?'background-color:#b8b8b8;':'' }}"
                                                            class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="6"
                                                            value="@if(old('qty'.$i)){{ old('qty'.$i) }}@else{{ 0 }}@endif" 
                                                            {{ $is_partial_received=='N'?'style="text-align: right;background-color:#b8b8b8;"':'' }} />
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td><label id="unit-{{ $i }}" for="" class="col-form-label">{{ $q->quantity_type }}</label></td>
                                                    <td>
                                                        <input @if($is_partial_received=='N'){!! 'readonly' !!}@endif type="text" 
                                                            style="text-align: right;{{ $is_partial_received=='N'?'background-color:#b8b8b8;':'' }}" 
                                                            onchange="formatPartPrice({{ $i }});"
                                                            class="form-control @error('price_part'.$i) is-invalid @enderror" id="price_part{{ $i }}" name="price_part{{ $i }}"
                                                            maxlength="64" value="@if(old('price_part'.$i)){{ old('price_part'.$i) }}@endif" />
                                                        @error('price_part'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        @php
                                                            $totalPrice = 0;
                                                        @endphp
                                                        @if(old('qty'.$i))
                                                            @php
                                                                $qty = old('qty'.$i);
                                                                $price_part = old('price_part'.$i);
                                                                if (is_numeric(str_replace(",", "", $qty)) && is_numeric(str_replace(",", "", $price_part))) {
                                                                    $totalPrice = $qty * str_replace(",", "", $price_part);
                                                                }
                                                            @endphp
                                                        @endif
                                                        <label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format($totalPrice,(old('supplier_type_id')==10?2:0),'.',',') }}</label>
                                                    </td>
                                                    <td>
                                                        <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" rows="3" class="form-control @error('desc_part'.$i) is-invalid @enderror"
                                                            style="width: 100%;">@if(old('desc_part'.$i)){{ old('desc_part'.$i) }}@endif</textarea>
                                                        @error('desc_part'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="final-cost-{{ $i }}" for=""
                                                            class="col-form-label">{{ (is_null($q)?'':number_format($q->final_cost,0,'.',',').' / '.(is_null($q->fobCurr)?'':$q->fobCurr->string_val).' '.number_format($q->final_fob,($orders->supplier_type_id==10?2:0),'.',',')) }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format($q->total_qty,0,'.',',') }}</label>
                                                        <input type="hidden" name="oh_{{ $i }}_tmp" id="oh_{{ $i }}_tmp" value="{{ $q->total_qty }}">
                                                    </td>
                                                    @php
                                                        $oo = 0;
                                                        if(!is_null($q)){
                                                            $oo = ($q->purchase_memo_qty+$q->purchase_order_qty)-($q->purchase_ro_qty+$q->purchase_ro_qty_no_partial);
                                                        }
                                                    @endphp
                                                    <td style="text-align: right;">
                                                        <label id="oo-{{ $i }}" for="" class="col-form-label">{{ number_format($oo,0,'.',',') }}</label>
                                                        <input type="hidden" name="oo_{{ $i }}_tmp" id="oo_{{ $i }}_tmp" value="{{ $oo }}">
                                                    </td>
                                                    <td style="text-align: center;">
                                                        {{-- @if($is_part_in_RO=='N'){!! '<input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}" style="vertical-align: middle;">' !!}@endif --}}
                                                    </td>
                                                </tr>
                                                @php
                                                    $iRowPart++;
                                                @endphp
                                            @endif
                                        @endfor

                                    @else

                                        @php
                                            $i=0;
                                        @endphp
                                        @foreach($orderParts AS $mp)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label for="" id="purchase_order_row_number{{ $i }}" class="col-form-label">{{ $i+1 }}.</label>
                                                    <input type="hidden" name="order_part_id_{{ $i }}" id="order_part_id_{{ $i }}" value="{{ $mp->id }}">
                                                </th>
                                                @php
                                                    // tampilkan QTY yg sudah masuk RO dan berstatus active
                                                    $sumQtyRO = \App\Models\Tx_receipt_order_part::whereIn('receipt_order_id', function($q) {
                                                        $q->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->where('active', 'Y');
                                                    })
                                                    ->where('po_mo_no', $orders->purchase_no)
                                                    ->where('po_mo_id', $mp->id)
                                                    ->where('part_id', $mp->part_id)
                                                    ->where('active', 'Y')
                                                    ->sum('qty');

                                                    $is_partial_received = 'Y';
                                                    $is_part_in_RO = $sumQtyRO>0?'Y':'N';
                                                    $qIsPartialReceived = \App\Models\Tx_receipt_order_part::select('is_partial_received')
                                                    ->whereIn('receipt_order_id', function($q) {
                                                        $q->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->where('active', 'Y');
                                                    })
                                                    ->where('po_mo_no', $orders->purchase_no)
                                                    ->where('po_mo_id', $mp->id)
                                                    ->where('part_id', $mp->part_id)
                                                    ->where('is_partial_received', 'N') // memastikan jika ada status partial received==N
                                                    ->where('active', 'Y')
                                                    ->first();
                                                    if ($qIsPartialReceived){
                                                        $is_partial_received = $qIsPartialReceived->is_partial_received;
                                                    }

                                                    $q = \App\Models\Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                                                    ->leftJoin('mst_globals as q_type', 'mst_parts.quantity_type_id', '=', 'q_type.id')
                                                    ->select(
                                                        'mst_parts.*',
                                                        'tx_qty_parts.qty as total_qty',
                                                        'q_type.title_ind AS quantity_type',
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
                                                        'mst_parts.id' => $mp->part_id,
                                                        'tx_qty_parts.branch_id' => $userLogin->branch_id
                                                    ])
                                                    ->first();
                                                @endphp
                                                <td>
                                                    @php
                                                        $partId = $mp->part_id;
                                                        $partName = '';
                                                        $partNumber = '';
                                                    @endphp
                                                    @if ($is_partial_received=='N' || $is_part_in_RO=='Y')
                                                        @php
                                                            $partOne = \App\Models\Mst_part::where([
                                                                'id' => $partId,
                                                            ])
                                                            ->first();
                                                            if ($partOne){
                                                                $partName = $partOne->part_name;
                                                                $partNumber = $partOne->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            }
                                                        @endphp
                                                        <input type="hidden" id="part_id{{ $i }}" name="part_id{{ $i }}" value="{{ $partId }}">
                                                        <label id="part_id{{ $i }}-lbl" for="" class="col-form-label">{{ $partNumber .' : '.$partName }}</label>
                                                    @else
                                                        <select class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                            id="part_id{{ $i }}" name="part_id{{ $i }}" onchange="dispPriceRef(this.value, {{ $i }});">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partList = \App\Models\Mst_part::where([
                                                                    'id' => $partId,
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
                                                                <option @if($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber .' : '.$pr->part_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('part_id'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    @endif
                                                </td>
                                                <td>
                                                    <input @if($is_partial_received=='N'){!! 'readonly' !!}@endif onchange="totalPrice({{ $i }});" type="text" 
                                                        class="form-control @error('qty'.$i) is-invalid @enderror"
                                                        id="qty{{ $i }}" name="qty{{ $i }}" maxlength="6"
                                                        value="{{ $mp->qty }}" 
                                                        style="text-align: right;@if($is_partial_received=='N'){!! 'background-color:#b8b8b8;' !!}@endif" />
                                                        {{-- {{ $sumQtyRO.'::'.$is_partial_received }} --}}
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td><label id="unit-{{ $i }}" for="" class="col-form-label">{{ $q->quantity_type }}</label></td>
                                                <td>
                                                    <input @if($is_partial_received=='N'){!! 'readonly' !!}@endif type="text" onchange="formatPartPrice({{ $i }});"
                                                        class="form-control @error('price_part'.$i) is-invalid @enderror"
                                                        id="price_part{{ $i }}" name="price_part{{ $i }}" maxlength="64"
                                                        value="@if($orders->supplier_type_id==10){{ number_format($mp->price,2,'.',',') }}@else{{ number_format($mp->price,0,'.',',') }}@endif" 
                                                        style="text-align: right;@if($is_partial_received=='N'){!! 'background-color:#b8b8b8;' !!}@endif" />
                                                    @error('price_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    @php
                                                        $qty = $mp->qty;
                                                        $price_part = $mp->price;
                                                        $totalPrice = 0;
                                                        if (is_numeric(str_replace(",", "", $qty)) && is_numeric(str_replace(",", "", $price_part))) {
                                                            $totalPrice = $qty * str_replace(",", "", $price_part);
                                                        }
                                                    @endphp
                                                    <label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format($totalPrice,($orders->supplier_type_id==10?2:0),'.',',') }}</label>
                                                </td>
                                                <td>
                                                    <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" rows="3" class="form-control @error('desc_part'.$i) is-invalid @enderror"
                                                        style="width: 100%;">{{ $mp->description }}</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="final-cost-{{ $i }}" for=""
                                                        class="col-form-label">{{ (!isset($q)?'':number_format($q->final_cost,0,'.',',').' / '.(is_null($q->fobCurr)?'':$q->fobCurr->string_val).number_format($q->final_fob,2,'.',',')) }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format($q->total_qty,0,'.',',') }}</label>
                                                    <input type="hidden" name="oh_{{ $i }}_tmp" id="oh_{{ $i }}_tmp" value="{{ $q->total_qty }}">
                                                </td>
                                                @php
                                                    $oo = 0;
                                                    if(!is_null($q)){
                                                        $oo = ($q->purchase_memo_qty+$q->purchase_order_qty)-($q->purchase_ro_qty+$q->purchase_ro_qty_no_partial);
                                                    }
                                                @endphp
                                                <td style="text-align: right;">
                                                    <label id="oo-{{ $i }}" for="" class="col-form-label">{{ number_format($oo,0,'.',',') }}</label>
                                                    <input type="hidden" name="oo_{{ $i }}_tmp" id="oo_{{ $i }}_tmp" value="{{ $oo }}">
                                                </td>
                                                <td style="text-align: center;">
                                                    {{-- @if($is_part_in_RO=='N'){!! '<input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}" style="vertical-align: middle;">' !!}@endif --}}
                                                </td>
                                            </tr>
                                            @php
                                                $i+= 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                {{-- <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row"> --}}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if($orders->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if($orders->created_by==Auth::user()->id && $orders->active=='Y' && is_null($orders->receipt_order) && is_null($orders->approved_by))
                                        <input type="hidden" name="orderId" id="orderId">
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                    @endif
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end row-->
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
    function dispPriceRef(part_id, idx){
        if($("#branch_id").val()==='#'){
            alert('Please select a valid branch');
            $("#new-row").empty();
            $("#totalRow").val(0);
            return false;
        }
        var fd = new FormData();
        fd.append('part_id', part_id);
        fd.append('porder_created_by', {{ $orders->created_by }});
        $.ajax({
            url: "{{ url('/disp_part_price_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let fob_curr = '';
                let final_fob = 0;
                let o = res[0].parts;
                if(o[0].fob_curr!==null){fob_curr = o[0].fob_curr}
                if(o[0].final_fob!==null){final_fob = o[0].final_fob}
                $('#final-cost-'+idx).html('{{ $qCurrency->string_val }}'+parseFloat(o[0].final_cost).numberFormat(0,'.',',')+' / '+fob_curr+parseFloat(final_fob).numberFormat(0,'.',','));
                $('#unit'+idx).text(o[0].quantity_type);
                $('#oh-'+idx).text(parseFloat(o[0].qty).numberFormat(0,'.',','));
                let oo = (parseInt(o[0].purchase_memo_qty)+parseInt(o[0].purchase_order_qty))-(parseInt(o[0].purchase_ro_qty)+parseInt(o[0].purchase_ro_qty_no_partial));
                $('#oo-'+idx).text(parseFloat(oo).numberFormat(0,'.',','));
            },
        });
    }

    function totalPrice(idx){
        let qty = parseInt($("#qty"+idx).val());
        let price = parseFloat($("#price_part"+idx).val().replaceAll(',',''));

        if(!isNaN(qty) && !isNaN(price)){
            let total = qty*price;
            $("#total-price-"+idx).text(total.numberFormat(0,'.',','));
        }
    }

    function formatPartPrice(idx){
        let validateChars = '0123456789,.';
        let priceList = $("#price_part"+idx).val().replaceAll(',','');
        for(let i=0;i<priceList.length;i++){
            if (validateChars.indexOf(priceList.substr(i, 1))==-1){
                $("#price_part"+idx).val(0);
                return false;
            }
        }
        if($('#supplier_type_id').val()==10){
            priceList = parseFloat(priceList).numberFormat(2,'.',',');
        }else{
            priceList = parseFloat(priceList).numberFormat(0,'.',',');
        }
        $("#price_part"+idx).val(priceList);

        // set cursor position
        // if($("#price_part"+idx).val().length>=3){
        //     $("#price_part"+idx).selectRange($("#price_part"+idx).val().length-3); // set cursor position
        // }

        totalPrice(idx)
    }

    function addPartFromPQ(partId,idx){
        var fd = new FormData();
        fd.append('part_id', partId);
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
                if (totParts > 0) {
                    let optionValue = '';
                    for (let i = 0; i < totParts; i++) {
                        let partNo = o[i].part_number;
                        if (partNo.length<11){
                            partNo = partNo.substring(0,5)+'-'+partNo.substring(5,partNo.length);
                        }else{
                            partNo = partNo.substring(0,5)+'-'+partNo.substring(5,5)+'-'+partNo.substring(10,partNo.length);
                        }
                        optionText=partNo+' : '+o[i].part_name;
                        optionValue=o[i].id;
                        $("#part_id"+idx).append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                    $("#part_id"+idx).val(optionValue).change();
                }
            },
        });
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

    function addPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;"><label for="" id="purchase_order_row_number'+totalRow+'" class="col-form-label">'+rowNo+'.</label></th>'+
            '<td>'+
            '<select class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'" onchange="dispPriceRef(this.value, '+totalRow+');">'+
            '<option value="#">Choose...</option>'+
            '</select>'+
            '</td>'+
            '<td>'+
            '<input type="text" style="text-align: right;" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="6" style="text-align: right;" />'+
            '</td>'+
            '<td><label id="unit'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td>'+
            '<input onchange="formatPartPrice('+totalRow+');" type="text" style="text-align: right;" class="form-control" id="price_part'+totalRow+'" '+
            'name="price_part'+totalRow+'" maxlength="64" style="text-align: right;" />'+
            '</td>'+
            '<td style="text-align: right;"><label id="total-price-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
            '<td><textarea name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" rows="3" class="form-control" style="width: 100%;"></textarea></td>'+
            '<td style="text-align: right;"><label id="final-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
            '<td style="text-align: right;">'+
            '<label id="oh-'+totalRow+'" for="" class="col-form-label">---</label>'+
            '<input type="hidden" name="oh_'+totalRow+'_tmp" id="oh_'+totalRow+'_tmp" value="0">'+
            '</td>'+
            '<td style="text-align: right;">'+
            '<label id="oo-'+totalRow+'" for="" class="col-form-label">---</label>'+
            '<input type="hidden" name="oo_'+totalRow+'_tmp" id="oo_'+totalRow+'_tmp" value="0">'+
            '</td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'" style="vertical-align: middle;"></td>'+
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);
    }

    $(document).ready(function() {
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
        @if($orders->created_by==Auth::user()->id && $orders->active=='Y' && is_null($orders->receipt_order) && is_null($orders->approved_by))
            $("#del-btn").click(function() {
                let msg = 'The following Order Numbers will be canceled.\n{{ $orders->purchase_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#orderId").val('{{ $orders->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_order') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();

        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#est_supply_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            addPart();

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#purchase_order_row_number"+i).text()){
                    $("#purchase_order_row_number"+i).text(j+'. ');
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
                if($("#purchase_order_row_number"+i).text()){
                    $("#purchase_order_row_number"+i).text(j+'. ');
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
        setPartsToDropdown();

        $("#courier_type").change(function() {
            if(parseInt($("#courier_type").val())===parseInt({{ env('COURIER') }})){
                $("#courier-list").css("display","block");
            }else{
                $("#courier-list").css("display","none");
            }
        });

        $('#quotation_id').change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);

            var fd = new FormData();
            fd.append('qId', $('#quotation_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_parts_by_pq') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].qParts;
                    let totPart = o.length;
                    if (totPart > 0) {
                        for (let i = 0; i < totPart; i++) {
                            addPart();
                            addPartFromPQ(o[i].part_id,i);

                            $("#part_id"+i).val(o[i].part_id).change();
                            $("#qty"+i).val(o[i].qty);
                            // $("#price_part"+i).val('0,00');
                            $("#desc_part"+i).val(o[i].description);
                        }
                        setPartsToDropdown();
                    }
                },
            });
        });

        $('#supplier_id').change(function() {
            var fd = new FormData();
            fd.append('id', $('#supplier_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_supplierinfo_by_id') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].suppliers;
                    $('#supplier_type_id').val(o.supplier_type_id);
                    if(o.supplier_type_id==10){
                        $('#price-rp').text('Price');
                    }else{
                        $('#price-rp').text('Price ({{ $qCurrency->string_val }})');
                    }
                },
            });

            $("#supplier_pic").empty();
            $("#supplier_pic").append(
                `<option value="#">Choose...</option>`
            );
            $("#currency_id").empty();
            $("#currency_id").append(
                `<option value="#">Choose...</option>`
            );

            dispSupplierPic('supplier_id', '#supplier_id option:selected', '{{ url("disp_supplier_pic") }}', '#supplier_pic');
            dispSupplierCurrency('supplier_id', '#supplier_id option:selected', '{{ url("disp_supplier_currency") }}', '#currency_id');

            $("#quotation_id").empty();
            $("#quotation_id").append(`<option value="#">Choose...</option>`);

            var fd = new FormData();
            fd.append('supplier_id', $('#supplier_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_pq_by_supplier') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].qPQ;
                    let totqPQ = o.length;
                    if (totqPQ > 0) {
                        for (let i = 0; i < totqPQ; i++) {
                            optionText = o[i].quotation_no;
                            optionValue = o[i].pq_id;
                            $("#quotation_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    }
                },
            });
        });
    });
</script>
@endsection
