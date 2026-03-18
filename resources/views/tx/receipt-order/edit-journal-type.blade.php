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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'-journal-type/'.$ro->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">RO No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $ro->receipt_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Date</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($ro->receipt_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->supplier->name }}</label>
                                            </div>
                                            @php
                                                $qSupplier = \App\Models\Mst_supplier::where('id','=',$ro->supplier_id)
                                                ->first();
                                                $currencySymbol = $qCurrency->string_val;
                                            @endphp
                                            @if($qSupplier->supplier_type_id==10)
                                                @php
                                                    $currencySymbol = $ro->currency->string_val;
                                                @endphp
                                            @endif
                                            <div class="row mb-3">
                                                <label for="invoice_no" class="col-sm-3 col-form-label">Invoice No</label>
                                                <label for="" class="col-sm-9 col-form-label part_id">{{ $ro->invoice_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label id="invoice_amount_lbl" for="invoice_amount" class="col-sm-3 col-form-label">
                                                    Invoice Amount{{ $currency_code!=""? " (".$currency_code.")": "" }}
                                                </label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ $currencySymbol.number_format($ro->invoice_amount,($ro->supplier->supplier_type_id==10?2:0),'.',',') }}
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="exc_rate" class="col-sm-3 col-form-label">Exchange Rate</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ $ro->exchange_rate>0?$qCurrency->string_val.number_format($ro->exchange_rate,2,'.',','):'' }}
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="exc_rate" class="col-sm-3 col-form-label">VAT Import</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->supplier_type_id==10?$qCurrency->string_val.number_format($ro->total_vat_rp,2,'.',','):'' }}</label>
                                            </div>
                                            {{-- <div class="row mb-3">
                                                <label for="exc_rate" class="col-sm-3 col-form-label">Exchange Rate VAT</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ $ro->exc_rate_for_vat>0?$qCurrency->string_val.number_format($ro->exc_rate_for_vat,2,'.',','):'' }}
                                                </label>
                                            </div> --}}
                                            <div class="row mb-3">
                                                <label id="import_shipping_cost_val_lbl" for="import_shipping_cost_val" class="col-sm-3 col-form-label">
                                                    Bea Masuk Import                                                    
                                                </label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->bea_masuk>0?$qCurrency->string_val.number_format($ro->bea_masuk,2,'.',','):'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="exc_rate" class="col-sm-3 col-form-label">Import Shipping Cost{{ $currency_code!=""? " (".$currency_code.")": "" }}</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    {{ $ro->import_shipping_cost>0?$currencySymbol.number_format($ro->import_shipping_cost,2,'.',','):'' }}
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="bl_no" class="col-sm-3 col-form-label">B/L No</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->bl_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="vessel_no" class="col-sm-3 col-form-label">Vessel No</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->vessel_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="weight_type_id01" class="col-sm-3 col-form-label">Weight Type</label>
                                                <label for="" class="col-sm-3 col-form-label">{{ !is_null($ro->weight_type_01)?$ro->weight_type_01->title_ind:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="weight_type_id02" class="col-sm-3 col-form-label">Dimension</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->weight_type_02)?$ro->weight_type_02->title_ind:'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">PO/MO No</label>
                                                <div class="col-sm-5">
                                                    <table class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 5%;text-align:center;vertical-align:middle;">#</th>
                                                                <th scope="col" style="width: 95%;" class="part-id">PO/MO No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-po-mo">
                                                            @php
                                                                $po_mo_no_arr = explode(",",$ro->po_or_pm_no);
                                                                $iRow = 0;
                                                                $is_vat = '';
                                                            @endphp
                                                            @for ($i=0;$i<count($po_mo_no_arr);$i++)
                                                                @if ($po_mo_no_arr[$i]!='')
                                                                    @if (strpos("-".$po_mo_no_arr[$i],env('P_PURCHASE_MEMO'))>0)
                                                                        @php
                                                                            $qMemo = \App\Models\Tx_purchase_memo::where([
                                                                                'memo_no'=>$po_mo_no_arr[$i],
                                                                            ])
                                                                            ->first();
                                                                            if ($qMemo){
                                                                                $is_vat = $qMemo->is_vat;
                                                                            }
                                                                        @endphp
                                                                    @endif
                                                                    @if (strpos("-".$po_mo_no_arr[$i],env('P_PURCHASE_ORDER'))>0)
                                                                        @php
                                                                            $qOrder = \App\Models\Tx_purchase_order::where([
                                                                                'purchase_no'=>$po_mo_no_arr[$i],
                                                                            ])
                                                                            ->first();
                                                                            if ($qOrder){
                                                                                $is_vat = $qOrder->is_vat;
                                                                            }
                                                                        @endphp
                                                                    @endif
                                                                    <tr id="row_po_mo_{{ $i }}">
                                                                        <th scope="row" style="text-align:right;vertical-align:middle;"><label for="" class="col-form-label">{{ $iRow+1 }}.</label></th>
                                                                        <td scope="row" style="text-align:left;">
                                                                            <label for="" id="s_po_mo_no{{ $i }}" class="col-form-label part-id">{{ $po_mo_no_arr[$i] }}</label>
                                                                        </td>
                                                                    </tr>
                                                                    @php
                                                                        $iRow++;
                                                                    @endphp
                                                                @endif
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="journal_type_id" class="col-sm-3 col-form-label">Journal Type</label>
                                                <div class="col-sm-6">
                                                    @php
                                                        $journal_type_id = (old('journal_type_id')?old('journal_type_id'):$ro->journal_type_id);
                                                    @endphp
                                                    <input type="hidden" name="journal_type_id_current" id="journal_type_id_current" value="{{ $ro->journal_type_id }}">
                                                    <select class="form-select single-select @error('journal_type_id') is-invalid @enderror" id="journal_type_id" name="journal_type_id">
                                                        <option value="">Choose...</option>
                                                        @foreach ($journal_type as $t)
                                                            <option @if($journal_type_id==$t){{ 'selected' }}@endif value="{{ $t }}">{{ $t }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('journal_type_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <label for="journal_type_id" id="journal_type_info" class="col-sm-3 col-form-label" style="color: red;font-weight:700;">
                                                    @if ($journal_type_id=='P'){{ 'Akan Dibayar PPN?' }}@endif
                                                    @if ($journal_type_id=='N'){{ 'Akan Dibayar Non PPN?' }}@endif
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="currency_name" class="col-sm-3 col-form-label">Currency</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($ro->currency)?$ro->currency->title_ind:'') }}</label>
                                            </div>
                                            @if ($ro->exc_rate_for_vat>0)
                                                <div class="row mb-3">
                                                    <label for="exc_rate_inv_amount" class="col-sm-3 col-form-label">Rp Amount</label>
                                                    <label for="" class="col-sm-3 col-form-label">
                                                        {{ number_format($ro->exchange_rate*$ro->invoice_amount,0,'.',',') }}
                                                    </label>
                                                </div>
                                            @else
                                                <div class="row mb-3">
                                                    <label for="exc_rate_inv_amount" class="col-sm-3 col-form-label">Rp Amount</label>
                                                    <label for="" class="col-sm-3 col-form-label">
                                                        {{ ($ro->exchange_rate*$ro->invoice_amount)>0?number_format($ro->exchange_rate*$ro->invoice_amount,0,'.',','):'' }}
                                                    </label>
                                                </div>
                                            @endif
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="courier_id" class="col-sm-3 col-form-label">Ship By</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    @switch($ro->courier_type)
                                                        @case(env('AMBIL_SENDIRI'))
                                                            {{ env('AMBIL_SENDIRI_STR') }}
                                                            @break
                                                        @case(env('DIANTAR'))
                                                            {{ env('DIANTAR_STR') }}
                                                            @break
                                                        @case(env('COURIER'))
                                                            {{ env('COURIER_STR').(!is_null($ro->courier)?' - '.$ro->courier->name:'') }}
                                                            @break
                                                        @default
                                                            {{ '' }}
                                                    @endswitch
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="shipto_name" class="col-sm-3 col-form-label">Ship To</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($ro->branch)?$ro->branch->name:'') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="gross_weight" class="col-sm-3 col-form-label">Gross Weight</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->gross_weight)?number_format($ro->gross_weight,0,'.',','):'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="measurement" class="col-sm-3 col-form-label">Measurement</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($ro->measurement)?number_format($ro->measurement,0,'.',','):'' }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $ro->remark }}</label>
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
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 5%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 15%;">Price FOB</th>
                                        <th scope="col" style="width: 15%;">Total FOB</th>
                                        <th scope="col" style="width: 15%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Total ({{ $qCurrency->string_val }})</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $lastTotalAmount = 0;
                                        $lastTotalFobAmount = 0;
                                    @endphp

                                    @php
                                        $lastIdx = 0;
                                    @endphp
                                    @foreach ($ro_part as $r_part)
                                        <tr id="row{{ $lastIdx }}">
                                            <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $lastIdx+1 }}</label></td>
                                            <td>
                                                @php
                                                    $qParts = \App\Models\Mst_part::where('id','=',$r_part->part_id)
                                                    ->first();

                                                    $partNumber = $qParts->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                <label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}"
                                                    class="col-form-label">{{ $partNumber.' : '.$qParts->part_name }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="qty{{ $lastIdx }}" id="qty{{ $lastIdx }}" class="col-form-label">{{ $r_part->qty }}</label>
                                            </td>
                                            @php
                                                $price_fob = 0;
                                                $price_local = 0;
                                                $currency_fob_tmp = !is_null($ro->currency)?$ro->currency->string_val:'';
                                                $exc_rate = 0;
                                                $exch_rate_for_vat = 0;
                                            @endphp
                                            @if ($qSupplier->supplier_type_id==10)
                                                {{-- international --}}
                                                @php
                                                    $exc_rate = $ro->exchange_rate;
                                                    $exch_rate_for_vat = $ro->exc_rate_for_vat;
                                                    $price_local = number_format($r_part->final_fob*$exc_rate,0,'.',',');
                                                    $total = number_format($r_part->qty*$r_part->final_fob*$exc_rate,0,'.',',');
                                                    $lastTotalAmount+= ($r_part->qty*$r_part->final_fob*$exc_rate);
                                                    $price_fob = number_format($r_part->final_fob,2,'.',',');
                                                    $lastTotalFobAmount += ($r_part->qty*$r_part->final_fob);
                                                @endphp
                                            @endif
                                            @if ($qSupplier->supplier_type_id==11)
                                                {{-- lokal --}}
                                                @php
                                                    $price_fob = 0;
                                                    $price_local = number_format($r_part->final_cost,0,'.',',');
                                                    $total = number_format($r_part->qty*$r_part->final_cost,0,'.',',');

                                                    $lastTotalFobAmount = 0;
                                                    $lastTotalAmount+= ($r_part->qty*$r_part->final_cost);
                                                @endphp
                                            @endif
                                            <td style="text-align: right;">
                                                <label for="" name="price_fob{{ $lastIdx }}" id="price_fob{{ $lastIdx }}" class="col-form-label">
                                                    {{ ($ro->supplier->supplier_type_id==10?$ro->currency->string_val:'').number_format($r_part->final_fob,($ro->supplier->supplier_type_id==10?2:0),'.',',') }}
                                                </label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total_fob{{ $lastIdx }}" id="total_fob{{ $lastIdx }}" class="col-form-label">
                                                    {{ ($ro->supplier->supplier_type_id==10?$ro->currency->string_val:'').number_format($r_part->qty*$r_part->final_fob,($ro->supplier->supplier_type_id==10?2:0),'.',',') }}
                                                </label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="price_local{{ $lastIdx }}" id="price_local{{ $lastIdx }}" class="col-form-label">{{ $price_local }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}" class="col-form-label">{{ $total }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $lastIdx += 1;
                                        @endphp
                                    @endforeach

                                    <tr id="rowTotal">
                                        <td colspan="4" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalFOBAmount" id="lblTotalFOBAmount" class="col-form-label">
                                                {{ ($ro->supplier->supplier_type_id==10?$ro->currency->string_val:'').number_format($lastTotalFobAmount,($ro->supplier->supplier_type_id==10?2:0),'.',',') }}
                                            </label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                    </tr>
                                    @php
                                        $bea_masuk = $ro->bea_masuk;
                                        $import_shipping_cost = $ro->import_shipping_cost;
                                    @endphp
                                    {{-- <tr id="rowBeaMasuk">
                                        <td colspan="4" style="text-align: right;">
                                            <label for="" name="lblBeaMasuk" id="lblBeaMasuk" class="col-form-label">Bea Masuk Import</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblBeaMasukFOBAmount" id="lblBeaMasukFOBAmount"
                                                class="col-form-label">{{ ($ro->supplier->supplier_type_id==10?$ro->currency->string_val:'').number_format(($bea_masuk>0?($bea_masuk/$exc_rate):0),2,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblBeaMasukAmount" id="lblBeaMasukAmount" class="col-form-label">{{ number_format($bea_masuk,0,'.',',') }}</label>
                                        </td>
                                    </tr> --}}
                                    <tr id="rowVAT">
                                        <td colspan="4" style="text-align: right;">
                                            <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblVATFOBAmount" id="lblVATFOBAmount" class="col-form-label">&nbsp;</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">
                                                @if ($qSupplier->supplier_type_id==10)
                                                    {{ number_format($ro->total_vat_rp,0,'.',',') }}
                                                @endif
                                                @if ($qSupplier->supplier_type_id==11)
                                                    {{ number_format(($is_vat=='Y'?($lastTotalAmount*$vat/100):0),0,'.',',') }}
                                                @endif
                                            </label>
                                        </td>
                                    </tr>
                                    <tr id="rowGrandTotal">
                                        <td colspan="4" style="text-align: right;">
                                            <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblGrandTotalFOBAmount" id="lblGrandTotalFOBAmount" class="col-form-label">&nbsp;</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">
                                                @if ($qSupplier->supplier_type_id==10)
                                                    {{ number_format($ro->total_after_vat_rp,0,'.',',') }}
                                                @endif
                                                @if ($qSupplier->supplier_type_id==11)
                                                    {{ number_format($lastTotalAmount+(($is_vat=='Y'?($lastTotalAmount*$vat/100):0)),0,'.',',') }}
                                                @endif
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    @php
                        // tagihan supplier
                        $qTagihanSupplier = \App\Models\Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tx_ts','tx_tagihan_supplier_details.tagihan_supplier_id','=','tx_ts.id')
                        ->select(
                            'tx_tagihan_supplier_details.receipt_order_id',
                        )
                        ->where([
                            'tx_tagihan_supplier_details.receipt_order_id' => $ro->id,
                            'tx_tagihan_supplier_details.active'=>'Y',
                            'tx_ts.active'=>'Y',
                        ])
                        ->first();

                        // payment voucher
                        $qPySupplier = \App\Models\Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as tx_pv','tx_payment_voucher_invoices.payment_voucher_id','=','tx_pv.id')
                        ->select(
                            'tx_payment_voucher_invoices.is_full_payment',
                        )
                        ->whereRaw('tx_pv.approved_by IS NOT NULL')
                        ->where([
                            'tx_payment_voucher_invoices.receipt_order_id' => $ro->id,
                            'tx_payment_voucher_invoices.active'=>'Y',
                            'tx_pv.active'=>'Y',
                        ])
                        ->orderBy('tx_pv.created_at','DESC')
                        ->first();
                    @endphp
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    @if (!$qTagihanSupplier && !$qPySupplier)
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
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#journal_type_id").change(function() {
            if ($("#journal_type_id").val()==='P'){
                $("#journal_type_info").text('Akan Dibayar PPN?');
            }
            if ($("#journal_type_id").val()==='N'){
                $("#journal_type_info").text('Akan Dibayar Non PPN?');
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
        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
