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
                @method("PUT")
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
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Surat Jalan No</label>
                                <div class="col-sm-9">
                                    <label for="" class="col-form-label part-id">{{ $orders->surat_jalan_no }}</label>
                                    <input type="hidden" name="surat_jalan_no" id="surat_jalan_no" class="@error('customer_id') is-invalid @enderror"
                                        value="{{ $orders->surat_jalan_no }}">
                                    @error('surat_jalan_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Surat Jalan Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->surat_jalan_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="customer_id" class="col-sm-3 col-form-label">Customer*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $customerId = (old('customer_id')?old('customer_id'):$orders->customer_id);
                                        @endphp
                                        @foreach ($customers as $p)
                                            <option @if ($customerId==$p->id){{ 'selected' }}@endif
                                                value="{{ $p->id }}">{{ $p->customer_unique_code.' - '.(!is_null($p->entity_type)?$p->entity_type->title_ind:'').' '.$p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="customer_data" class="row mb-3">
                                <label for="customer_data" class="col-sm-3 col-form-label">Information</label>
                                <div id="customer_info" class="col-sm-9">
                                    @if($custInfo)
                                        {!!
                                        'Address:<br />'.$custInfo->office_address.
                                        ($custInfo->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($custInfo->subdistrict->sub_district_name))).
                                        ($custInfo->district->district_name=='Other'?'':', '.$custInfo->district->district_name).
                                        ($custInfo->city->city_name=='Other'?'':'<br />'.($custInfo->city->city_type=='Luar Negeri'?'':$custInfo->city->city_type).' '.
                                        $custInfo->city->city_name).
                                        ($custInfo->province->province_name=='Other'?'':'<br />'.$custInfo->province->province_name).'<br />'.$custInfo->province->country->country_name.
                                        ($custInfo->subdistrict->post_code=='000000'?'':' '.$custInfo->subdistrict->post_code)
                                        !!}
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="is_director" id="is_director" value="{{ $userLogin->is_director }}">
                            @if ($userLogin->is_director=='Y')
                            <div class="row mb-3">
                                <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $branchId = (old('branch_id')?old('branch_id'):$orders->branch_id);
                                        @endphp
                                        @foreach ($branches as $p)
                                            <option @if($branchId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @else
                            <input type="hidden" name="branch_id" id="branch_id" value="@if (old('branch_id')){{ old('branch_id') }}@else{{ $userLogin->branch_id }}@endif">
                            @endif
                            <div class="row mb-3">
                                <label for="customer_id" class="col-sm-3 col-form-label">SQ No</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('sales_quotation_no') is-invalid @enderror" id="sales_quotation_no" name="sales_quotation_no">
                                        <option value="#">Choose...</option>
                                        @php
                                            $sqId = (old('sales_quotation_no')?old('sales_quotation_no'):$orders->sales_quotation_id);
                                        @endphp
                                        @foreach ($qSQno as $p)
                                            <option @if ($sqId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->sales_quotation_no }}</option>
                                        @endforeach
                                    </select>
                                    @error('sales_quotation_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cust_pic" class="col-sm-3 col-form-label">PIC*</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('cust_pic') is-invalid @enderror" id="cust_pic" name="cust_pic">
                                        <option value="#">Choose...</option>
                                        @php
                                            $custPic = (old('cust_pic')?old('cust_pic'):$orders->pic_id);
                                        @endphp
                                        @if (old('cust_pic')!='#')
                                            <option @if ($custPic==1) {{ 'selected' }} @endif value="1">{{ !is_null($custInfo)?$custInfo->pic1_name:'' }}</option>
                                            @if(!is_null($custInfo))
                                                <option @if ($custPic==2) {{ 'selected' }} @endif value="2">{{ $custInfo->pic2_name }}</option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('cust_pic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cust_doc_no" class="col-sm-3 col-form-label">Cust Doc No</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control @error('cust_doc_no') is-invalid @enderror" maxlength="255" id="cust_doc_no"
                                        name="cust_doc_no" placeholder="Enter Customer Doc No"
                                        value="@if (old('cust_doc_no')){{ old('cust_doc_no') }}@else{{ $orders->customer_doc_no }}@endif">
                                    @error('cust_doc_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cust_unit_no" class="col-sm-3 col-form-label">Cust Unit No</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control @error('cust_unit_no') is-invalid @enderror" maxlength="255" id="cust_unit_no"
                                        name="cust_unit_no" placeholder="Enter Customer Unit No"
                                        value="@if (old('cust_unit_no')){{ old('cust_unit_no') }}@else{{ $orders->cust_unit_no }}@endif">
                                    @error('cust_unit_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cust_shipment_address" class="col-sm-3 col-form-label">Shipment Address*</label>
                                <div class="col-sm-9">
                                    <select class="form-select @error('cust_shipment_address') is-invalid @enderror" id="cust_shipment_address" name="cust_shipment_address">
                                        <option value="#">Choose...</option>
                                        @php
                                            $shipmentId = (old('cust_shipment_address')?old('cust_shipment_address'):$orders->cust_shipment_address);
                                        @endphp
                                        @foreach ($custShipmentAddressInfo as $b)
                                            @php
                                                $address = $b->address.' '.
                                                    ($b->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($b->subdistrict->sub_district_name))).
                                                    ($b->district->district_name=='Other'?'':', '.$b->district->district_name).
                                                    ($b->city->city_name=='Other'?'':' '.($b->city->city_type=='Luar Negeri'?'':$b->city->city_type).' '.$b->city->city_name).
                                                    ($b->province->province_name=='Other'?'':' '.$b->province->province_name).' '.$b->province->country->country_name.
                                                    ($b->subdistrict->post_code=='000000'?'':' '.$b->subdistrict->post_code);
                                            @endphp
                                            <option @if ((int)$shipmentId==(int)$b->id){{ 'selected' }}@endif value="{{ $b->id }}">{{ $address }}</option>
                                        @endforeach
                                    </select>
                                    @error('cust_shipment_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                                        @foreach ($couriers as $b)
                                            <option @if($courierId==$b->id) {{ 'selected' }} @endif value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('courier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="customer_pic" class="col-sm-3 col-form-label">Remark</label>
                                <div class="col-sm-9">
                                    <textarea name="salesRemark" id="salesRemark" rows="3" style="width: 100%;"
                                        class="form-control @error('salesRemark') is-invalid @enderror">@if (old('salesRemark')){{ old('salesRemark') }}@else{{ $orders->remark }}@endif</textarea>
                                    @error('salesRemark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="submit" class="btn btn-light px-5" style="margin-top: 15px;" value="Submit">
                                </div>
                            </div> --}}
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
                                        <th scope="col" style="width: 15%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 15%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 12%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Final Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">SO</th>
                                        <th scope="col" style="width: 13%;">Description</th>
                                        <th scope="col" style="width: 2%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if(old('totalRow'))
                                        @php
                                            $iRow = 1;
                                        @endphp
                                        @for ($i = 0; $i < $totRow; $i++)
                                            @if (old('part_id'.$i))  
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label id="surat-jalan-part{{ $i }}" for="" class="col-form-label">{{ $iRow }}.</label>
                                                        <input type="hidden" name="order_part_id{{ $i }}" id="order_part_id{{ $i }}" value="{{ old('order_part_id'.$i) }}">
                                                    </th>
                                                    @php
                                                        $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                                                        ->select(
                                                            'mst_parts.*',
                                                            'tx_qty.qty AS qty_oh'
                                                        )
                                                        ->selectRaw('(SELECT avg_cost 
                                                            FROM v_log_avg_cost 
                                                            WHERE part_id='.old('part_id'.$i).' 
                                                            AND updated_at<(SELECT created_at FROM tx_surat_jalans WHERE id='.$orders->id.') 
                                                            ORDER BY updated_at DESC 
                                                            LIMIT 1) as last_avg_cost')
                                                        ->where([
                                                            'mst_parts.id' => old('part_id'.$i),
                                                            'tx_qty.branch_id' => old('branch_id')
                                                        ])
                                                        ->first();

                                                        $qtySO = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                                        ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                                        ->whereNotIn('txso.id',function (\Illuminate\Database\Query\Builder $query) {
                                                            $query->select('tx_do_parts.sales_order_id')
                                                            ->from('tx_delivery_order_parts as tx_do_parts')
                                                            ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                            ->where('tx_do_parts.active','=','Y')
                                                            ->where('tx_do.active','=','Y');
                                                        })
                                                        ->where('tx_sales_order_parts.part_id','=',old('part_id'.$i))
                                                        ->where('tx_sales_order_parts.active','=','Y')
                                                        ->where('txso.sales_order_no','NOT LIKE','%Draft%')
                                                        // ->where('txso.need_approval','=','N')
                                                        ->where('txso.active','=','Y')
                                                        ->when(old('branch_id'), function($q) {
                                                            $q->whereRaw('((usr.branch_id='.old('branch_id').' AND txso.branch_id IS null) OR txso.branch_id='.old('branch_id').')');
                                                        })
                                                        ->sum('tx_sales_order_parts.qty');

                                                        $so = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                        ->leftJoin('userdetails AS usr','txso.created_by','=','usr.user_id')
                                                        ->select(
                                                            'tx_surat_jalan_parts.qty',
                                                            'txso.surat_jalan_no',
                                                        )
                                                        ->where('tx_surat_jalan_parts.part_id','=',old('part_id'.$i))
                                                        ->where('tx_surat_jalan_parts.active','=','Y')
                                                        ->where('txso.surat_jalan_no','NOT LIKE','%Draft%')
                                                        ->where('txso.branch_id','=',old('branch_id'))
                                                        ->where('txso.active','=','Y')
                                                        ->get();
                                                        foreach ($so as $so_part) {
                                                            $qDO = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                                            ->where('sales_order_no_all','LIKE','%'.$so_part->surat_jalan_no.'%')
                                                            ->where('active','=','Y')
                                                            ->first();
                                                            if(!$qDO){
                                                                $qtySO += $so_part->qty;
                                                            }
                                                        }

                                                        $qLastSO = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                        ->select(
                                                            'tx_surat_jalan_parts.last_avg_cost AS last_avg_cost',
                                                        )
                                                        ->where('tx_surat_jalan_parts.part_id','=',old('part_id'.$i))
                                                        ->where('tx_surat_jalan_parts.active','=','Y')
                                                        ->where('txso.id','=',$orders->id)
                                                        ->where('txso.active','=','Y')
                                                        ->first();
                                                    @endphp
                                                    <td>
                                                        @php
                                                            $partId = old('part_id'.$i);
                                                            $partList = \App\Models\Mst_part::where([
                                                                'id' => $partId,
                                                            ])
                                                            ->first();
                                                        @endphp
                                                        @if ($partList)
                                                            <label id="part-id-lbl-{{ $i }}" for="" class="col-form-label">
                                                                {{ $partList->part_number.' : '.$partList->part_name }}
                                                            </label>
                                                        @endif
                                                        <input type="hidden" name="part_id{{ $i }}" id="part_id{{ $i }}" value="{{ $partId }}" 
                                                            class="@error('part_id'.$i) is-invalid @enderror">
                                                        {{-- <select onchange="dispPartRef(this.value, {{ $i }});"
                                                            class="form-select single-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                            id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partId = old('part_id'.$i);
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
                                                                <option @if ($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                            @endforeach
                                                        </select> --}}
                                                        @error('part_id'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" onkeyup="totPrice({{ $i }});" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="6" value="{{ old('qty'.$i) }}" style="text-align: right;" />
                                                        <input type="hidden" id="qty_oh_{{ $i }}" name="qty_oh_{{ $i }}" value="{{ $partNo?$partNo->qty_oh:0 }}" />
                                                        <input type="hidden" id="initial_amount{{ $i }}" name="initial_amount{{ $i }}" value="{{ old('initial_amount'.$i) }}" />
                                                        @error('qty'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" onkeyup="formatAmount($(this));totPrice({{ $i }});" class="form-control @error('price'.$i) is-invalid @enderror"
                                                            id="price{{ $i }}" name="price{{ $i }}" maxlength="25" value="{{ old('price'.$i) }}" style="text-align: right;"/>
                                                        @error('price'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    @php
                                                        $price = old('price'.$i);
                                                        $price = str_replace($qCurrency->string_val,"",$price);
                                                        $price = str_replace(",","",$price);
                                                    @endphp
                                                    <td style="text-align: right;"><label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format((!is_null(old('qty'.$i))?(int)old('qty'.$i):0)*(is_numeric($price)?$price:0),0,'.',',') }}</label></td>
                                                    <td style="text-align: right;">
                                                        <label id="final-cost-{{ $i }}" for="" class="col-form-label">
                                                            {{-- {{ (!$qLastSO?(is_null($partNo)?0:($partNo->last_avg_cost?$partNo->last_avg_cost:$partNo->avg_cost)):
                                                                number_format($qLastSO->last_avg_cost,0,'.',',')) }} --}}
                                                            {{ (!$qLastSO?(is_null($partNo)?0:($partNo->last_avg_cost!=null?number_format($partNo->last_avg_cost,0,'.',','):number_format($partNo->avg_cost,0,'.',','))):
                                                                number_format($qLastSO->last_avg_cost,0,'.',',')) }}
                                                        </label>
                                                    </td>
                                                    <td style="text-align: right;"><label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_price,0,'.',',')) }}</label></td>
                                                    <input type="hidden" name="avg_cost_{{ $i }}_db" id="avg_cost_{{ $i }}-db" 
                                                        value="{{ (!$qLastSO?(is_null($partNo)?0:($partNo->last_avg_cost!=null?$partNo->last_avg_cost:$partNo->avg_cost)):$qLastSO->last_avg_cost) }}">
                                                    {{-- <input type="hidden" name="avg_cost_{{ $i }}_db" id="avg_cost_{{ $i }}-db" value="{{ (is_null($partNo)?0:($partNo->last_avg_cost?$partNo->last_avg_cost:$partNo->avg_cost)) }}"> --}}
                                                    <td style="text-align: right;"><label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format(($partNo?$partNo->qty_oh:0),0,'.',',') }}</label></td>
                                                    <td style="text-align: right;"><label id="so-{{ $i }}" for="" class="col-form-label">{{ number_format($qtySO,0,'.',',') }}</label></td>
                                                    <td>
                                                        <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" class="form-control" rows="3" style="width: 100%;" maxlength="1024">{{ old('desc_part'.$i) }}</textarea>
                                                        @error('desc_part'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                                </tr>
                                                @php
                                                    $iRow++;
                                                @endphp
                                            @endif
                                        @endfor

                                    @else

                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($order_parts as $op)

                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label id="surat-jalan-part{{ $i }}" for="" class="col-form-label">{{ $i + 1 }}.</label>
                                                    <input type="hidden" name="order_part_id{{ $i }}" id="order_part_id{{ $i }}" value="{{ $op->id }}">
                                                </th>
                                                @php
                                                    $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                                                    ->select(
                                                        'mst_parts.*',
                                                        'tx_qty.qty AS qty_oh'
                                                    )
                                                    ->where([
                                                        'mst_parts.id' => $op->part_id,
                                                        'tx_qty.branch_id' => $orders->branch_id,
                                                    ])
                                                    ->first();

                                                    $qtySO = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                                    ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                                    ->whereNotIn('txso.id',function (\Illuminate\Database\Query\Builder $query) {
                                                        $query->select('tx_do_parts.sales_order_id')
                                                        ->from('tx_delivery_order_parts as tx_do_parts')
                                                        ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                                        ->where('tx_do_parts.active','=','Y')
                                                        ->where('tx_do.active','=','Y');
                                                    })
                                                    ->where('tx_sales_order_parts.part_id','=',$op->part_id)
                                                    ->where('tx_sales_order_parts.active','=','Y')
                                                    ->where('txso.sales_order_no','NOT LIKE','%Draft%')
                                                    // ->where('txso.need_approval','=','N')
                                                    ->where('txso.active','=','Y')
                                                    ->when(old('branch_id'), function($q) {
                                                        $q->whereRaw('((usr.branch_id='.old('branch_id').' AND txso.branch_id IS null) OR txso.branch_id='.old('branch_id').')');
                                                    })
                                                    ->sum('tx_sales_order_parts.qty');

                                                    $so = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                    ->leftJoin('userdetails AS usr','txso.created_by','=','usr.user_id')
                                                    ->select(
                                                        'tx_surat_jalan_parts.qty',
                                                        'txso.surat_jalan_no',
                                                    )
                                                    ->where('tx_surat_jalan_parts.part_id','=',$op->part_id)
                                                    ->where('tx_surat_jalan_parts.active','=','Y')
                                                    ->where('txso.surat_jalan_no','NOT LIKE','%Draft%')
                                                    ->where('txso.branch_id','=',$orders->branch_id)
                                                    ->where('txso.active','=','Y')
                                                    // ->where('usr.branch_id','=',$orders->branch_id)
                                                    ->get();
                                                    foreach ($so as $so_part) {
                                                        $qDO = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                                        ->where('sales_order_no_all','LIKE','%'.$so_part->surat_jalan_no.'%')
                                                        ->where('active','=','Y')
                                                        ->first();
                                                        if(!$qDO){
                                                            $qtySO += $so_part->qty;
                                                        }
                                                    }

                                                    $qLastSO = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                    ->select(
                                                        'tx_surat_jalan_parts.last_avg_cost AS last_avg_cost',
                                                    )
                                                    ->where('tx_surat_jalan_parts.part_id','=',$op->part_id)
                                                    ->where('tx_surat_jalan_parts.active','=','Y')
                                                    ->where('txso.id','=',$orders->id)
                                                    ->where('txso.active','=','Y')
                                                    ->first();
                                                @endphp
                                                <td>
                                                    @php
                                                        $partId = $op->part_id;
                                                        $partList = \App\Models\Mst_part::where([
                                                            'id' => $partId,
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    @if ($partList)
                                                        <label id="part-id-lbl-{{ $i }}" for="" class="col-form-label">
                                                            {{ $partList->part_number.' : '.$partList->part_name }}
                                                        </label>
                                                    @endif
                                                    <input type="hidden" name="part_id{{ $i }}" id="part_id{{ $i }}" value="{{ $partId }}" 
                                                        class="@error('part_id'.$i) is-invalid @enderror">
                                                    {{-- <select onchange="dispPartRef(this.value, {{ $i }});"
                                                        class="form-select single-select partsAjax @error('part_id'.$i) is-invalid @enderror" id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partId = $op->part_id;
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
                                                            <option @if ($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                        @endforeach
                                                    </select> --}}
                                                    @error('part_id'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" onkeyup="totPrice({{ $i }});" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                        id="qty{{ $i }}" name="qty{{ $i }}" maxlength="6" value="{{ $op->qty }}" style="text-align: right;" />
                                                    <input type="hidden" id="qty_oh_{{ $i }}" name="qty_oh_{{ $i }}" value="{{ $partNo?$partNo->qty_oh:0 }}" />
                                                    <input type="hidden" id="initial_amount{{ $i }}" name="initial_amount{{ $i }}" value="{{ $op->qty }}" />
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" onkeyup="formatAmount($(this));totPrice({{ $i }});" class="form-control @error('price'.$i) is-invalid @enderror"
                                                        id="price{{ $i }}" name="price{{ $i }}" maxlength="25" value="{{ number_format($op->price,0,'.',',') }}" style="text-align: right;"/>
                                                    @error('price'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;"><label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format($op->qty*$op->price,0,'.',',') }}</label></td>
                                                <td style="text-align: right;">
                                                    <label id="final-cost-{{ $i }}" for="" class="col-form-label">
                                                        {{ (!$qLastSO?0:number_format($qLastSO->last_avg_cost,0,'.',',')) }}
                                                    </label>
                                                </td>
                                                <td style="text-align: right;"><label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_price,0,'.',',')) }}</label></td>
                                                <input type="hidden" name="avg_cost_{{ $i }}_db" id="avg_cost_{{ $i }}-db" value="{{ $qLastSO->last_avg_cost }}">
                                                <td style="text-align: right;"><label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format(($partNo?$partNo->qty_oh:0),0,'.',',') }}</label></td>
                                                <td style="text-align: right;"><label id="so-{{ $i }}" for="" class="col-form-label">{{ number_format($qtySO,0,'.',',') }}</label></td>
                                                <td>
                                                    <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" class="form-control" rows="3" style="width: 100%;" maxlength="1024">{{ $op->desc }}</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                {{-- <td style="text-align: right;"><label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->price_list,0,'.',',')) }}</label></td> --}}
                                                <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;display:none;" value="Add Row">
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
                                    @if ($orders->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if ($orders->created_by==Auth::user()->id && $orders->active=='Y' && is_null($orders->delivery_order) && is_null($orders->approved_by))
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
    function formatAmount(elm){
        let amount = elm.val().replaceAll(',','');
        if(amount===''){elm.val('');return false;}
        if(isNaN(amount)){elm.val('');return false;}
        amount = parseFloat(amount).numberFormat(0,'.',',');
        elm.val(amount);

        // set cursor position
        console.log(elm.val().length);
        // if(elm.val().length>=3){
        //     elm.selectRange(elm.val().length-3); // set cursor position
        // }
    }

    function totPrice(idx){
        let qtyTemp = 0;
        let priceTemp = 0;
        if($.isNumeric($('#qty'+idx).val())){
            qtyTemp = $('#qty'+idx).val();
        }
        if($.isNumeric($('#price'+idx).val().replaceAll(",", ""))){
            priceTemp = $('#price'+idx).val().replaceAll(",", "");
        }
        $('#total-price-'+idx).text(parseFloat(qtyTemp*priceTemp).numberFormat(0,'.',','));
    }

    function dispPartRef(part_id, idx){
        if ($('#is_director').val()==='Y' && $('#branch_id option:selected').val()==='#'){
            alert('Please select a valid branch');
            $("#new-row").empty();
            $("#totalRow").val(0);
            return false;
        }

        var fd = new FormData();
        fd.append('sj_id', {{ $orders->id }});
        fd.append('part_id', part_id);
        if ($('#is_director').val()==='Y' && $('#branch_id option:selected').val()!=='#'){
            fd.append('branch_id', $('#branch_id option:selected').val());
        }else{
            fd.append('branch_id', {{ $userLogin->branch_id }});
        }
        $.ajax({
            url: "{{ url('/disp_sj_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                let p = res[0].soQty;
                let qty_oh = 0;
                $('#qty'+idx).val(0);
                $('#price'+idx).val(0);
                $('#total-price-'+idx).text(0);
                if (typeof(o[0]) !== 'undefined') {
                    qty_oh = o[0].total_qty;
                    $('#qty_oh_'+idx).text(qty_oh);
                    $('#part_type-'+idx).text(o[0].part_type_name);
                    $('#final-cost-'+idx).text(parseFloat(o[0].avg_cost).numberFormat(0,'.',','));
                    $('#final-price-'+idx).text(parseFloat(o[0].final_price).numberFormat(0,'.',','));
                    $('#avg_cost_'+idx+'-db').val((o[0].last_avg_cost!=null?o[0].last_avg_cost:o[0].avg_cost));
                    // $('#avg_cost_'+idx+'-db').val(o[0].avg_cost);
                    $('#oh-'+idx).text(parseFloat(o[0].total_qty).numberFormat(0,'.',','));
                    $('#so-'+idx).text(parseFloat(p).numberFormat(0,'.',','));
                }else{
                    $('#qty_oh_'+idx).text(0);
                    $('#part_type-'+idx).text('');
                    $('#final-cost-'+idx).text('0');
                    $('#final-price-'+idx).text('0');
                    $('#avg_cost_'+idx+'-db').val(0);
                    $('#oh-'+idx).text(0);
                    $('#so-'+idx).text(0);
                    // $('#price_list-'+idx).text('0');
                }
            },
        });
    }

    function dispCustomerInfoById(custid){
        var fd = new FormData();
        fd.append('id', custid);
        $.ajax({
            url: "{{ url('/disp_custinfo_byid') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].customers;

                let address = o[0].office_address;
                let sub_district = o[0].sub_district_name.trim();
                let post_code = ' '+o[0].post_code;
                if(o[0].sub_district_name=='Other'){
                    sub_district = '';
                    post_code = '';
                }
                let district = ', '+o[0].district_name.trim();
                if(o[0].district_name=='Other'){
                    district = '';
                }
                let city_type = o[0].city_type+' ';
                if(o[0].city_type=='Luar Negeri'){
                    city_type = '';
                }
                let city = '<br />'+city_type+o[0].city_name;
                let province = '<br />'+o[0].province_name;
                if(o[0].province_name=='Other'){
                    province = '';
                }
                let country = '<br />'+o[0].country_name;

                let custInfo = 'Address:<br/>'+address+' '+sub_district.toLowerCase().ucwords().trim()+district+city+province+country+post_code;
                $('#customer_info').html(custInfo);

                optionText=o[0].pic1_name;
                optionValue=1;
                $("#cust_pic").append(`<option value="${optionValue}">${optionText}</option>`);

                if(o[0].pic2_name!=null){
                    optionText=o[0].pic2_name;
                    optionValue=2;
                    $("#cust_pic").append(`<option value="${optionValue}">${optionText}</option>`);
                }
            },
        });
    }

    function dispSQno(cid){
        var fd = new FormData();
        fd.append('cid', cid);
        $.ajax({
            url: "{{ url('/disp_sq_cust_sj') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].sq;
                let totSQ = o.length;

                $("#sales_quotation_no").empty();
                $("#sales_quotation_no").append(`<option value="#">Choose...</option>`);

                if (totSQ > 0) {
                    for (let i = 0; i < totSQ; i++) {
                        optionText = o[i].sales_quotation_no;
                        optionValue = o[i].id;
                        $("#sales_quotation_no").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            },
        });
    }

    function dispCustomerShipmentAddress(custid){
        var fd = new FormData();
        fd.append('id', custid);
        $.ajax({
            url: "{{ url('/disp_custinfo_shipment_address') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].customers;
                let totCustomers = o.length;
                if (totCustomers > 0) {
                    for (let i = 0; i < totCustomers; i++) {
                        let address = o[i].shipment_address;
                        let sub_district = o[i].sub_district_name.trim();
                        let post_code = ' '+o[i].post_code;
                        if(o[i].sub_district_name=='Other'){
                            sub_district = '';
                            post_code = '';
                        }
                        let district = ', '+o[i].district_name.trim();
                        if(o[i].district_name=='Other'){
                            district = '';
                        }
                        let city = ', '+o[i].city_name;
                        let province = ', '+o[i].province_name;
                        if(o[i].province_name=='Other'){
                            province = '';
                        }
                        let country = ', '+o[i].country_name;
                        let custInfo = address+' '+sub_district.toLowerCase().ucwords().trim()+district+city+province+country+post_code;

                        optionText=custInfo;
                        optionValue=o[i].shipment_id;
                        $("#cust_shipment_address").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
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
        let rowNo = (parseInt(totalRow) + 1);
        let vHtml = '<tr id="row' + totalRow + '">' +
            '<th scope="row" style="text-align:right;"><label id="surat-jalan-part'+totalRow+'" for="" class="col-form-label">' + rowNo + '.</label></th>' +
            '<input type="hidden" name="order_part_id'+totalRow+'" id="order_part_id'+totalRow+'">'+
            '<td>'+
            '<select onchange="dispPartRef(this.value, '+totalRow+');" class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'">'+
            '<option value="#">Choose...</option>'+
            '</select>'+
            '</td>'+
            '<td>'+
            '<input type="text" onkeyup="totPrice('+totalRow+');" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="6" style="text-align: right;" />'+
            '<input type="hidden" id="qty_oh_'+totalRow+'" name="qty_oh_'+totalRow+'" />'+
            '</td>'+
            '<td>'+
            '<input type="text" onkeyup="formatAmount($(this));totPrice('+totalRow+');" class="form-control" id="price'+totalRow+'" name="price'+totalRow+'" '+
            'maxlength="25" style="text-align: right;" value="" />'+
            '<input type="hidden" name="initial_amount'+totalRow+'" id="initial_amount'+totalRow+'">'+
            '</td>'+
            '<td style="text-align: right;"><label id="total-price-'+totalRow+'" for="" class="col-form-label">---</label></td>' +
            '<td style="text-align: right;"><label id="final-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>' +
            '<td style="text-align: right;"><label id="final-price-'+totalRow+'" for="" class="col-form-label">---</label></td>' +
            '<input type="hidden" name="avg_cost_'+totalRow+'_db" id="avg_cost_'+totalRow+'-db" value="0">'+
            '<td style="text-align: right;"><label id="oh-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
            '<td style="text-align: right;"><label id="so-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
            '<td><textarea class="form-control" name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" rows="3" style="width: 100%;" maxlength="1024"></textarea></td>' +
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    }

    function addPartFromSQ(partId,idx){
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

    function validatePriceToAVGcost(){
        let msg = '';
        if($("#totalRow").val()>0){
            for(let i=0;i<$("#totalRow").val();i++){
                if (typeof($("#price"+i).val()) !== 'undefined') {
                    if($("#price"+i).val()!==''){
                        let price = $("#price"+i).val().replaceAll(",","");
                        if(parseFloat(price)<parseFloat($("#avg_cost_"+i+"-db").val())){
                            msg = 'Information:\n1. If the price entered is less than the AVG Cost, it requires approval.\n\n';
                            return msg;
                        }
                    }else{
                        // kosong
                    }
                }else{
                    // undefined
                }
            }
        }

        return msg;
    }

    $(document).ready(function() {
        // @if(!old('customer_id') || old('customer_id')=='#')
        //     $("#customer_data").hide();
        // @endif

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
            if(!confirm(validatePriceToAVGcost()+"Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
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
            location.href='{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });
        @if ($orders->created_by==Auth::user()->id && $orders->active=='Y' && is_null($orders->delivery_order) && is_null($orders->approved_by))
            $("#del-btn").click(function() {
                let msg = 'The following Surat Jalan Numbers will be canceled.\n{{ $orders->surat_jalan_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#orderId").val('{{ $orders->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_suratjalan') }}");
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
            $('#order_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            addPart();
            setPartsToDropdown();

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#surat-jalan-part"+i).text()){
                    $("#surat-jalan-part"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#branch_id").change(function() {
            $("#new-row").empty();
            $("#totalRow").val(0);
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#surat-jalan-part"+i).text()){
                    $("#surat-jalan-part"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#courier_type").change(function() {
            if(parseInt($("#courier_type").val())===parseInt({{ env('COURIER') }})){
                $("#courier-list").css("display","block");
            }else{
                $("#courier-list").css("display","none");
            }
        });

        $('#sales_quotation_no').change(function() {
            if ($('#is_director').val()==='Y' && $('#branch_id option:selected').val()==='#'){
                alert('Please select a valid branch');
                $("#new-row").empty();
                $("#totalRow").val(0);
                return false;
            }
            $("#new-row").empty();
            $("#totalRow").val(0);

            var fd = new FormData();
            fd.append('sId', $('#sales_quotation_no option:selected').val());
            $.ajax({
                url: "{{ url('/disp_parts_by_sq') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].sParts;
                    let totPart = o.length;
                    if (totPart > 0) {
                        for (let i = 0; i < totPart; i++) {
                            addPart();
                            addPartFromSQ(o[i].part_id,i);

                            // $("#part-no-"+i).val(o[i].part_id).change();
                            $("#part_id"+i).val(o[i].part_id).change();
                            $("#qty"+i).val(o[i].qty).change();
                            $("#initial_amount"+i).val(o[i].qty).change();
                            $("#price"+i).val(parseFloat(o[i].price_part).numberFormat(0,'.',',')).change();
                            $("#desc_part"+i).val(o[i].description).change();
                        }
                        setPartsToDropdown();
                    }
                },
            });
        });

        $('#customer_id').change(function() {
            $("#cust_pic").empty();
            $("#cust_pic").append(
                `<option value="#">Choose...</option>`
            );

            $("#customer_data").show();
            dispCustomerInfoById($('#customer_id option:selected').val());

            $("#cust_shipment_address").empty();
            $("#cust_shipment_address").append(
                `<option value="#">Choose...</option>`
            );
            dispCustomerShipmentAddress($('#customer_id option:selected').val());

            dispSQno($('#customer_id option:selected').val());
            $("#new-row").empty();
            $("#totalRow").val(0);

            // munculkan tombol Add Part setelah ganti customer
            $("#btn-add-row").css("display", "block");
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
