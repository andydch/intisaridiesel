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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($qNotaRetur->nota_retur_no)) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-8">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">NR No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qNotaRetur->nota_retur_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->customer->name }}</label>
                                                <input type="hidden" name="customer_id" value="{{ $qNotaRetur->customer_id }}">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="faktur_id" class="col-sm-3 col-form-label">FK No*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('faktur_id') is-invalid @enderror" id="faktur_id" name="faktur_id"
                                                        onchange="dispSObyCust(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('faktur_id')?old('faktur_id'):$qNotaRetur->delivery_order_id);
                                                        @endphp
                                                        @foreach ($qDeliveryOrder as $qC)
                                                            <option @if($p_Id==$qC->id){{ 'selected' }}@endif value="{{ $qC->id }}">{{ $qC->delivery_order_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('faktur_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="sales_order_id" class="col-sm-3 col-form-label">SO No*</label>
                                                <div class="col-sm-5">
                                                    <select class="form-select single-select @error('all_selected_SO') is-invalid @enderror" id="sales_order_id" name="sales_order_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $so_Id = (old('sales_order_id')?old('sales_order_id'):0);
                                                        @endphp
                                                        @foreach ($so as $qS)
                                                            <option @if ($so_Id==$qS->id) {{ 'selected' }} @endif value="{{ $qS->id }}">{{ $qS->sales_order_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('all_selected_SO')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="button" id="gen-part" class="btn btn-primary px-5" value="Generate Part">
                                                </div>
                                            </div>
                                            <input type="hidden" name="totRowSO" id="totRowSO" value="@if(old('totRowSO')){{ old('totRowSO') }}@else{{ $totRowSO }}@endif">
                                            <input type="hidden" name="all_selected_SO" id="all_selected_SO" value="@if(old('all_selected_SO')){{ old('all_selected_SO') }}@else{{ $all_selected_SO }}@endif">
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 47%;text-align:center;">SO No</th>
                                                                <th scope="col" style="width: 47%;text-align:center;">Cust Doc No</th>
                                                                <th scope="col" style="width: 3%;text-align:center;">Delete</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-so">
                                                            @php
                                                                $iRow=0;
                                                                $iRowNo=0;
                                                                $all_selected_SO=explode(",",(old('all_selected_SO')?old('all_selected_SO'):$all_selected_SO));
                                                            @endphp
                                                            @if (old('all_selected_SO'))
                                                                @for ($lastCounter=0;$lastCounter<count($all_selected_SO);$lastCounter++)
                                                                    @if ($all_selected_SO[$lastCounter]!='')
                                                                        @php
                                                                            $iRow+=1;
                                                                            $qSO = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_selected_SO[$lastCounter])
                                                                            ->first();
                                                                        @endphp
                                                                        <tr id="rowSO{{ $lastCounter }}">
                                                                            <td scope="row" style="text-align:right;">
                                                                                <label for="" id="sales_order_row_number{{ $lastCounter }}" class="col-form-label">{{ $iRowNo }}.</label>
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="so_no_{{ $lastCounter }}" id="so_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $all_selected_SO[$lastCounter] }}</label>
                                                                                <input type="hidden" name="so_id_{{ $lastCounter }}" id="so_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                            </td>
                                                                            <td style="text-align: center;"><input type="checkbox" id="rowSOCheck{{ $lastCounter }}" value="{{ $lastCounter }}"></td>
                                                                        </tr>
                                                                        @php
                                                                            $iRowNo++;
                                                                        @endphp
                                                                    @endif
                                                                @endfor
                                                            @else
                                                                @for ($lastCounter=0;$lastCounter<count($all_selected_SO);$lastCounter++)
                                                                    @if ($all_selected_SO[$lastCounter]!='')
                                                                        @php
                                                                            $iRow+=1;
                                                                            $qSO = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_selected_SO[$lastCounter])
                                                                            ->first();
                                                                        @endphp
                                                                        <tr id="rowSO{{ $lastCounter }}">
                                                                            <td scope="row" style="text-align:right;">
                                                                                <label for="" id="sales_order_row_number{{ $lastCounter-1 }}" class="col-form-label">{{ $iRow }}.</label>
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="so_no_{{ $lastCounter }}" id="so_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $all_selected_SO[$lastCounter] }}</label>
                                                                                <input type="hidden" name="so_id_{{ $lastCounter }}" id="so_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                            </td>
                                                                            <td style="text-align: center;"><input type="checkbox" id="rowSOCheck{{ $lastCounter }}" value="{{ $lastCounter }}"></td>
                                                                        </tr>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                    <input type="button" id="del-row-so" class="btn btn-danger px-5" value="Remove Row" style="margin-top: 5px;">
                                                </div>
                                            </div>
                                            <input type="hidden" name="is_director" id="is_director" value="{{ $userLogin->is_director }}">
                                            <input type="hidden" name="branch_id" id="branch_id" value="@if (old('branch_id')){{ old('branch_id') }}@else{{ $qNotaRetur->branch_id }}@endif">
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@else{{ $qNotaRetur->remark }}@endif</textarea>
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
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 15%;">Part No</th>
                                        <th scope="col" style="width: 15%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 5%;">Qty Retur</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 5%;">SO No</th>
                                        <th scope="col" style="width: 3%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row-so-part">
                                    @php
                                        $lastIdx = 0;
                                        $totalPrice = 0;
                                        $lastSoPartCounter=0;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if(old('part_id_'.$lastIdx))
                                                <tr id="rowSOdetail{{ $lastIdx }}">
                                                    @php
                                                        $parts = \App\Models\Tx_sales_order_part::leftJoin('mst_parts AS mp','tx_sales_order_parts.part_id','=','mp.id')
                                                        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
                                                        ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
                                                        ->leftJoin('tx_sales_orders AS tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                                                        ->select(
                                                            'tx_sales_order_parts.id AS sales_order_part_id',
                                                            'tx_sales_order_parts.order_id AS sales_order_id',
                                                            'tx_sales_order_parts.part_id',
                                                            'tx_sales_order_parts.part_no',
                                                            'tx_sales_order_parts.qty',
                                                            'tx_sales_order_parts.price',
                                                            'mp.part_name',
                                                            'mg01.string_val AS part_unit',
                                                            'mp.weight',
                                                            'mg02.string_val AS weight_unit',
                                                            'tx_so.sales_order_no',
                                                        )
                                                        ->where([
                                                            'tx_sales_order_parts.id' => old('sales_order_part_id'.$lastIdx),
                                                            'tx_sales_order_parts.active' => 'Y'
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    @if ($parts)
                                                        @php
                                                            $totalPrice += ($parts->price*old('qty_retur'.$lastSoPartCounter));
                                                            // $totalPrice += ($parts->price*$parts->qty);

                                                            $partNumber = $parts->part_no;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="row_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $lastSoPartCounter+1 }}.</label>
                                                            <input type="hidden" id="so_id_linktopart_{{ $lastSoPartCounter }}" name="so_id_linktopart_{{ $lastSoPartCounter }}"
                                                                value="{{ old('so_id_linktopart_'.$lastIdx) }}">
                                                            <input type="hidden" id="sales_order_part_id{{ $lastSoPartCounter }}" name="sales_order_part_id{{ $lastSoPartCounter }}"
                                                                value="{{ old('sales_order_part_id'.$lastIdx) }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="part_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                            <input type="hidden" id="part_id_{{ $lastSoPartCounter }}" name="part_id_{{ $lastSoPartCounter }}"
                                                                value="{{ $parts->part_id }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="part_name_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="qty_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                            <input type="hidden" id="qty_do_{{ $lastSoPartCounter }}" name="qty_do_{{ $lastSoPartCounter }}" value="{{ $parts->qty }}">
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <input type="text" id="qty_retur{{ $lastSoPartCounter }}" name="qty_retur{{ $lastSoPartCounter }}" style="text-align:right;"
                                                                class="form-control @error('qty_retur'.$lastSoPartCounter) is-invalid @enderror" maxlength="12"
                                                                value="@if (old('qty_retur'.$lastSoPartCounter)){{ old('qty_retur'.$lastSoPartCounter) }}@endif"
                                                                onchange="countTotal(this.value,{{ $lastSoPartCounter }});">
                                                            @error('qty_retur'.$lastSoPartCounter)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="unit_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_unit }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="price_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($parts->price,0,'.',',') }}</label>
                                                            <input type="hidden" id="price_ori_{{ $lastSoPartCounter }}" name="price_ori_{{ $lastSoPartCounter }}"
                                                                value="{{ $parts->price }}">
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($parts->price*old('qty_retur'.$lastSoPartCounter),0,'.',',') }}</label>
                                                            <input type="hidden" id="total_ori_{{ $lastSoPartCounter }}" name="total_ori_{{ $lastSoPartCounter }}" value="{{ $parts->price*old('qty_retur'.$lastSoPartCounter) }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="so_no_linktopart_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->sales_order_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:center;">
                                                            <input type="checkbox" id="rowCheck{{ $lastSoPartCounter }}" value="{{ $lastSoPartCounter }}">
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endif
                                            @php
                                                $lastSoPartCounter++;
                                            @endphp
                                        @endfor
                                    @else
                                        @foreach ($qNotaReturPart as $qNRpart)
                                            <tr id="rowSOdetail{{ $lastSoPartCounter }}">
                                                @php
                                                    $parts = \App\Models\Tx_sales_order_part::leftJoin('mst_parts AS mp','tx_sales_order_parts.part_id','=','mp.id')
                                                    ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
                                                    ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
                                                    ->leftJoin('tx_sales_orders AS tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                                                    ->select(
                                                        'tx_sales_order_parts.id AS sales_order_part_id',
                                                        'tx_sales_order_parts.order_id AS sales_order_id',
                                                        'tx_sales_order_parts.part_id',
                                                        'tx_sales_order_parts.part_no',
                                                        'tx_sales_order_parts.qty',
                                                        'tx_sales_order_parts.price',
                                                        'mp.part_name',
                                                        'mg01.string_val AS part_unit',
                                                        'mp.weight',
                                                        'mg02.string_val AS weight_unit',
                                                        'tx_so.sales_order_no',
                                                    )
                                                    ->where([
                                                        'tx_sales_order_parts.id' => $qNRpart->sales_order_part_id,
                                                        'tx_sales_order_parts.active' => 'Y'
                                                    ])
                                                    ->first();
                                                @endphp
                                                @if ($parts)
                                                    @php
                                                        $totalPrice += ($parts->price*$qNRpart->qty_retur);
                                                        // $totalPrice += ($parts->price*$parts->qty);

                                                        $partNumber = $parts->part_no;
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                    @endphp
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="row_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $lastSoPartCounter+1 }}.</label>
                                                        <input type="hidden" id="so_id_linktopart_{{ $lastSoPartCounter }}" name="so_id_linktopart_{{ $lastSoPartCounter }}"
                                                            value="{{ $parts->sales_order_id }}">
                                                        <input type="hidden" id="sales_order_part_id{{ $lastSoPartCounter }}" name="sales_order_part_id{{ $lastSoPartCounter }}"
                                                            value="{{ $qNRpart->sales_order_part_id }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="part_no_{{ $lastSoPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                        <input type="hidden" id="part_id_{{ $lastSoPartCounter }}" name="part_id_{{ $lastSoPartCounter }}"
                                                            value="{{ $parts->part_id }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="part_name_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="qty_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                        <input type="hidden" id="qty_do_{{ $lastSoPartCounter }}" name="qty_do_{{ $lastSoPartCounter }}" value="{{ $parts->qty }}">
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <input type="text" id="qty_retur{{ $lastSoPartCounter }}" name="qty_retur{{ $lastSoPartCounter }}" style="text-align:right;"
                                                            class="form-control @error('qty_retur'.$lastSoPartCounter) is-invalid @enderror" maxlength="12"
                                                            value="{{ $qNRpart->qty_retur }}"
                                                            onchange="countTotal(this.value,{{ $lastSoPartCounter }});">
                                                        @error('qty_retur'.$lastSoPartCounter)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="unit_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->part_unit }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="price_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price,0,'.',',') }}</label>
                                                        <input type="hidden" id="price_ori_{{ $lastSoPartCounter }}" name="price_ori_{{ $lastSoPartCounter }}"
                                                            value="{{ $parts->price }}">
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_{{ $lastSoPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price*$qNRpart->qty_retur,0,'.',',') }}</label>
                                                        <input type="hidden" id="total_ori_{{ $lastSoPartCounter }}" name="total_ori_{{ $lastSoPartCounter }}" value="{{ $qNRpart->final_price*$qNRpart->qty_retur }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="so_no_linktopart_{{ $lastSoPartCounter }}" class="col-form-label">{{ $parts->sales_order_no }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:center;">
                                                        <input type="checkbox" id="rowCheck{{ $lastSoPartCounter }}" value="{{ $lastSoPartCounter }}">
                                                    </td>
                                                @endif
                                            </tr>
                                            @php
                                                $lastSoPartCounter++;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" style="text-align: right;">Total before VAT</td>
                                        <td style="text-align: right;">
                                            <label for="" id="total_before_vat">{{ $qCurrency->string_val.number_format($totalPrice,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: right;">VAT</td>
                                        <td style="text-align: right;">
                                            <label for="" id="vat_total">{{ $qCurrency->string_val.number_format($totalPrice*$vat/100,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: right;">Grand Total</td>
                                        <td style="text-align: right;">
                                            <label for="" id="grand_total">{{ $qCurrency->string_val.number_format($totalPrice+($totalPrice*$vat/100),0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="input-group">
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
                                    @if ($qNotaRetur->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if ($qNotaRetur->created_by==Auth::user()->id && $qNotaRetur->active=='Y' && is_null($qNotaRetur->approved_by))
                                        <input type="hidden" name="returId" id="returId">
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
    function countTotal(qty_retur,idx){
        if(!isNaN(qty_retur)){
            let finalPrice = $("#price_ori_"+idx).val();
            $("#total_ori_"+idx).val(finalPrice*qty_retur);
            $("#total_"+idx).text(parseFloat(finalPrice*qty_retur).numberFormat(0,'.',','));

            countGrandTotal();
        }
    }

    function dispFKandSObyCust(custId){
        if(custId!=='#'){
            $("#new-row-so").empty();
            $("#new-row-so-part").empty();
            $("#faktur_id").empty();
            $("#faktur_id").append(`<option value="#">Choose...</option>`);
            $("#sales_order_id").empty();
            $("#sales_order_id").append(`<option value="#">Choose...</option>`);

            var fd = new FormData();
            fd.append('cust_id', custId);
            $.ajax({
                url: "{{ url('/disp_fk_so_by_cust') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].fakturs;
                    let totFK = o.length;
                    if (totFK > 0) {
                        for (let i = 0; i < totFK; i++) {
                            optionText = o[i].delivery_order_no;
                            optionValue = o[i].id;
                            $("#faktur_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        }
    }

    function dispSObyCust(fk_Id){
        if(fk_Id!='#'){
            $("#sales_order_id").empty();
            $("#sales_order_id").append(`<option value="#">Choose...</option>`);

            var fd = new FormData();
            fd.append('fk_id', fk_Id);
            $.ajax({
                url: "{{ url('/disp_so_by_cust') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let s = res[0].salesorders;
                    let totSO = s.length;
                    if (totSO > 0) {
                        for (let i = 0; i < totSO; i++) {
                            optionText = s[i].sales_order_no;
                            optionValue = s[i].id;
                            $("#sales_order_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        }
    }

    function addrowSO(so_id,so_no){
        let lastCounter = $("#totRowSO").val();
        var fd = new FormData();
        fd.append('so_id', so_id);
        $.ajax({
            url: "{{ url('/disp_so_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let s = res[0].salesorders;
                $("#branch_id").val(s.branch_id);

                vHtml = '<tr id="rowSO'+lastCounter+'">'+
                    '<td scope="row" style="text-align:right;"><label for="" id="sales_order_row_number'+lastCounter+'" class="col-form-label">'+(parseInt(lastCounter)+1)+'.</label></td>'+
                    '<td>'+
                    '<label for="" name="so_no_'+lastCounter+'" id="so_no_'+lastCounter+'" class="col-form-label">'+so_no+'</label>'+
                    '<input type="hidden" name="so_id_'+lastCounter+'" id="so_id'+lastCounter+'" value="'+so_id+'">'+
                    '</td>'+
                    '<td>'+
                    '<label for="" name="cust_doc_no_'+lastCounter+'" id="cust_doc_no_'+lastCounter+'" class="col-form-label">'+s.customer_doc_no+'</label>'+
                    '</td>'+
                    '<td style="text-align: center;"><input type="checkbox" id="rowSOCheck'+lastCounter+'" value="'+lastCounter+'"></td>'+
                    '</tr>';
                $("#new-row-so").append(vHtml);
                $("#totRowSO").val(parseInt(lastCounter)+1);

                // reset penomoran
                j = 1;
                for (i = 0; i < $("#totRowSO").val(); i++) {
                    if($("#sales_order_row_number"+i).text()){
                        $("#sales_order_row_number"+i).text(j+'. ');
                        j++;
                    }
                }
                // reset penomoran - end
            },
        });

        let allSO = $("#all_selected_SO").val()+','+so_no;
        $("#all_selected_SO").val(allSO);

        // prepare SO detail
        var fd = new FormData();
        fd.append('order_id', so_id);
        $.ajax({
            url: "{{ url('/disp_so_part') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                let totPart = o.length;
                let lastSoPartCounter = $("#totalRow").val();
                if (totPart > 0) {
                    for (let i = 0; i < totPart; i++) {
                        let partNumber = o[i].part_no;
                        if(partNumber.length<11){
                            partNumber = partNumber.substring(0, 5)+'-'+partNumber.substring(5, partNumber.length);
                        }else{
                            partNumber = partNumber.substring(0, 5)+'-'+partNumber.substring(5, 5)+'-'+partNumber.substring(10, partNumber.length);
                        }
                        vHtml = '<tr id="rowSOdetail'+lastSoPartCounter+'">'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="row_no_'+lastSoPartCounter+'" class="col-form-label">'+(parseInt(lastSoPartCounter)+1)+'.</label>'+
                            '<input type="hidden" id="so_id_linktopart_'+lastSoPartCounter+'" name="so_id_linktopart_'+lastSoPartCounter+'" value="'+so_id+'">'+
                            '<input type="hidden" id="sales_order_part_id'+lastSoPartCounter+'" name="sales_order_part_id'+lastSoPartCounter+'" value="'+o[i].sales_order_part_id+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="part_no_'+lastSoPartCounter+'" class="col-form-label">'+partNumber+'</label>'+
                            '<input type="hidden" id="part_id_'+lastSoPartCounter+'" name="part_id_'+lastSoPartCounter+'" value="'+o[i].part_id+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="part_name_'+lastSoPartCounter+'" class="col-form-label">'+o[i].part_name+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="qty_'+lastSoPartCounter+'" class="col-form-label">'+o[i].qty+'</label>'+
                            '<input type="hidden" id="qty_do_'+lastSoPartCounter+'" name="qty_do_'+lastSoPartCounter+'" value="'+o[i].qty+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<input type="text" id="qty_retur'+lastSoPartCounter+'" name="qty_retur'+lastSoPartCounter+'" style="text-align:right;" class="form-control" '+
                            'maxlength="12" value="" onchange="countTotal(this.value,'+lastSoPartCounter+');">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="unit_'+lastSoPartCounter+'" class="col-form-label">'+o[i].part_unit+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="price_'+lastSoPartCounter+'" class="col-form-label">'+(parseFloat(o[i].price)).numberFormat(0,'.',',')+'</label>'+
                            '<input type="hidden" id="price_ori_'+lastSoPartCounter+'" name="price_ori_'+lastSoPartCounter+'" value="'+o[i].price+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="total_'+lastSoPartCounter+'" class="col-form-label"></label>'+
                            '<input type="hidden" id="total_ori_'+lastSoPartCounter+'" name="total_ori_'+lastSoPartCounter+'" value="0">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="so_no_linktopart_'+lastSoPartCounter+'" class="col-form-label">'+so_no+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:center;"><input type="checkbox" id="rowCheck'+lastSoPartCounter+'" value="'+lastSoPartCounter+'"></td>'+
                            '</tr>';
                        $("#new-row-so-part").append(vHtml);
                        lastSoPartCounter=parseInt(lastSoPartCounter)+1;
                    }
                    $("#totalRow").val(lastSoPartCounter);
                    countGrandTotal();
                }

                // reset penomoran
                j = 1;
                for (i = 0; i < $("#totalRow").val(); i++) {
                    if($("#row_no_"+i).text()){
                        $("#row_no_"+i).text(j+'. ');
                        j++;
                    }
                }
                // reset penomoran - end
            }
        });
    }

    function countGrandTotal(){
        let totalPrice = 0;
        for (i = 0; i < $("#totalRow").val(); i++) {
            if(!isNaN($("#total_ori_"+i).val())){
                totalPrice = parseFloat(totalPrice)+parseFloat($("#total_ori_"+i).val());
            }
        }
        $("#total_before_vat").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice)).numberFormat(0,'.',','));
        $("#vat_total").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice*{{ $vat }}/100)).numberFormat(0,'.',','));
        $("#grand_total").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice+(totalPrice*{{ $vat }}/100))).numberFormat(0,'.',','));
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
            
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        @if ($qNotaRetur->created_by==Auth::user()->id && $qNotaRetur->active=='Y' && is_null($qNotaRetur->approved_by))
            $("#del-btn").click(function() {
                let msg = 'The following Nota Retur Numbers will be canceled.\n{{ $qNotaRetur->nota_retur_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#returId").val('{{ $qNotaRetur->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_notaretur') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#rowSOdetail"+i).remove();
                }
            }
            countGrandTotal();

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowSO").val(); i++) {
                if($("#sales_order_row_number"+i).text()){
                    $("#sales_order_row_number"+i).text(j+'. ');
                    j++;
                }
            }

            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#row_no_"+i).text()){
                    $("#row_no_"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#gen-part").click(function() {
            let so_id = $("#sales_order_id option:selected").val();
            let so_no = $("#sales_order_id option:selected").text();
            for(let i=0;i<$("#totRowSO").val();i++){
                if($("#so_no_"+i).text()===so_no){
                    alert('The SO number already exists.');
                    return false;
                }
            }
            if(so_id!=='#'){
                addrowSO(so_id,so_no);
            }
        });

        $("#del-row-so").click(function() {
            let SOno2Del = '';
            for (i=0;i<$("#totRowSO").val(); i++) {
                if ($("#rowSOCheck"+i).is(':checked')) {
                    SOno2Del = $("#so_no_"+i).text();
                    $("#rowSO"+i).remove();
                    $("#all_selected_SO").val($("#all_selected_SO").val().replaceAll(','+SOno2Del,''));

                    for (j=0; j<$("#totalRow").val(); j++) {
                        if($("#so_no_linktopart_"+j).text()===SOno2Del){
                            $("#rowSOdetail"+j).remove();
                        }
                    }
                }
            }
            countGrandTotal();

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowSO").val(); i++) {
                if($("#sales_order_row_number"+i).text()){
                    $("#sales_order_row_number"+i).text(j+'. ');
                    j++;
                }
            }

            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#row_no_"+i).text()){
                    $("#row_no_"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            // let totalPrice = 0;
            // for (i = 0; i < $("#totalRow").val(); i++) {
            //     totalPrice = parseFloat(totalPrice)+parseFloat($("#total_ori_"+i).val());
            // }
            // if(isNaN(totalPrice)){
            //     $("#total_before_vat").text('{{ $qCurrency->string_val }}0');
            //     $("#vat_total").text('{{ $qCurrency->string_val }}0');
            //     $("#grand_total").text('{{ $qCurrency->string_val }}0');
            // }else{
            //     $("#total_before_vat").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice)).numberFormat(0,'.',','));
            //     $("#vat_total").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice*{{ $vat }}/100)).numberFormat(0,'.',','));
            //     $("#grand_total").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice+(totalPrice*{{ $vat }}/100))).numberFormat(0,'.',','));
            // }
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
