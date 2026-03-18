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
                                                <label for="" class="col-sm-3 col-form-label">RE No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qNotaRetur->nota_retur_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->customer->name }}</label>
                                                <input type="hidden" name="customer_id" value="{{ $qNotaRetur->customer_id }}">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="nota_penjualan_id" class="col-sm-3 col-form-label">NP No*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('nota_penjualan_id') is-invalid @enderror" id="nota_penjualan_id" name="nota_penjualan_id" 
                                                        onchange="dispSJbyCust(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('nota_penjualan_id')?old('nota_penjualan_id'):$qNotaRetur->delivery_order_id);
                                                        @endphp
                                                        @foreach ($qDeliveryOrder as $qC)
                                                            <option @if($p_Id==$qC->id){{ 'selected' }}@endif value="{{ $qC->id }}">{{ $qC->delivery_order_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('nota_penjualan_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="surat_jalan_id" class="col-sm-3 col-form-label">SJ No*</label>
                                                <div class="col-sm-5">
                                                    <select class="form-select single-select @error('all_selected_SJ') is-invalid @enderror" id="surat_jalan_id" name="surat_jalan_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $sj_id = (old('surat_jalan_id')?old('surat_jalan_id'):0);
                                                        @endphp
                                                        @foreach ($sj as $qS)
                                                            <option @if ($sj_id==$qS->id) {{ 'selected' }} @endif value="{{ $qS->id }}">{{ $qS->surat_jalan_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('all_selected_SJ')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="button" id="gen-part" class="btn btn-primary px-5" value="Generate Part">
                                                </div>
                                            </div>
                                            <input type="hidden" name="totRowSJ" id="totRowSJ" value="@if(old('totRowSJ')){{ old('totRowSJ') }}@else{{ $totRowSJ }}@endif">
                                            <input type="hidden" name="all_selected_SJ" id="all_selected_SJ" value="@if(old('all_selected_SJ')){{ old('all_selected_SJ') }}@else{{ $all_selected_SJ }}@endif">
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 47%;text-align:center;">SJ No</th>
                                                                <th scope="col" style="width: 47%;text-align:center;">Cust Doc No</th>
                                                                <th scope="col" style="width: 3%;text-align:center;">Delete</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-so">
                                                            @if (old('all_selected_SJ'))
                                                                @php
                                                                    $iRow=0;
                                                                    $all_selected_SJ=explode(",",old('all_selected_SJ'));
                                                                @endphp
                                                                @for ($lastCounter=0;$lastCounter<count($all_selected_SJ);$lastCounter++)
                                                                    @if ($all_selected_SJ[$lastCounter]!='')
                                                                        @php
                                                                            $iRow+=1;
                                                                            $qSO = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_selected_SJ[$lastCounter])
                                                                            ->first();
                                                                        @endphp
                                                                        <tr id="rowSJ{{ $lastCounter }}">
                                                                            <td scope="row" style="text-align:right;">
                                                                                <label for="" id="surat-jalan-no{{ $lastCounter }}" class="col-form-label">{{ $iRow }}.</label>
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="sj_no_{{ $lastCounter }}" id="sj_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $all_selected_SJ[$lastCounter] }}</label>
                                                                                <input type="hidden" name="sj_id_{{ $lastCounter }}" id="sj_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                            </td>
                                                                            <td style="text-align: center;vertical-align: middle;"><input type="checkbox" id="rowSJCheck{{ $lastCounter }}" value="{{ $lastCounter }}"></td>
                                                                        </tr>
                                                                    @endif
                                                                @endfor
                                                            @else
                                                                @php
                                                                    $iRow=0;
                                                                    $all_selected_SJ=explode(",",$all_selected_SJ);
                                                                @endphp
                                                                @for ($lastCounter=0;$lastCounter<count($all_selected_SJ);$lastCounter++)
                                                                    @if ($all_selected_SJ[$lastCounter]!='')
                                                                        @php
                                                                            $iRow+=1;
                                                                            $qSO = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_selected_SJ[$lastCounter])
                                                                            ->first();
                                                                        @endphp
                                                                        <tr id="rowSJ{{ $lastCounter }}">
                                                                            <td scope="row" style="text-align:right;">
                                                                                <label for="" id="surat-jalan-no{{ $lastCounter-1 }}" class="col-form-label">{{ $iRow }}.</label>
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="sj_no_{{ $lastCounter }}" id="sj_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $all_selected_SJ[$lastCounter] }}</label>
                                                                                <input type="hidden" name="sj_id_{{ $lastCounter }}" id="sj_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                            </td>
                                                                            <td>
                                                                                <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                                    class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                            </td>
                                                                            <td style="text-align: center;vertical-align: middle;"><input type="checkbox" id="rowSJCheck{{ $lastCounter }}" value="{{ $lastCounter }}"></td>
                                                                        </tr>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                    <input type="button" id="del-row-sj" class="btn btn-danger px-5" value="Remove Row" style="margin-top: 5px;">
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
                                        <th scope="col" style="width: 5%;">SJ No</th>
                                        <th scope="col" style="width: 3%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row-sj-part">
                                    @php
                                        $lastIdx = 0;
                                        $totalPrice = 0;
                                        $lastSjPartCounter=0;
                                        $iRowPart=0;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if(old('part_id_'.$lastIdx))
                                                <tr id="rowSJdetail{{ $lastIdx }}">
                                                    @php
                                                        $parts = \App\Models\Tx_surat_jalan_part::leftJoin('mst_parts AS mp','tx_surat_jalan_parts.part_id','=','mp.id')
                                                        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
                                                        ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
                                                        ->leftJoin('tx_surat_jalans AS tx_so','tx_surat_jalan_parts.surat_jalan_id','=','tx_so.id')
                                                        ->select(
                                                            'tx_surat_jalan_parts.id AS surat_jalan_part_id',
                                                            'tx_surat_jalan_parts.surat_jalan_id AS surat_jalan_id',
                                                            'tx_surat_jalan_parts.part_id',
                                                            'tx_surat_jalan_parts.part_no',
                                                            'tx_surat_jalan_parts.qty',
                                                            'tx_surat_jalan_parts.price',
                                                            'mp.part_name',
                                                            'mg01.string_val AS part_unit',
                                                            'mp.weight',
                                                            'mg02.string_val AS weight_unit',
                                                            'tx_so.surat_jalan_no',
                                                        )
                                                        ->where([
                                                            'tx_surat_jalan_parts.id' => old('surat_jalan_part_id'.$lastIdx),
                                                            'tx_surat_jalan_parts.active' => 'Y'
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    @if ($parts)
                                                        @php
                                                            $totalPrice += ($parts->price*old('qty_retur'.$lastSjPartCounter));

                                                            $partNumber = $parts->part_no;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="row_no_{{ $lastSjPartCounter }}" class="col-form-label">{{ $iRowPart }}.</label>
                                                            <input type="hidden" id="sj_id_linktopart_{{ $lastSjPartCounter }}" name="sj_id_linktopart_{{ $lastSjPartCounter }}"
                                                                value="{{ old('sj_id_linktopart_'.$lastIdx) }}">
                                                            <input type="hidden" id="surat_jalan_part_id{{ $lastSjPartCounter }}" name="surat_jalan_part_id{{ $lastSjPartCounter }}"
                                                                value="{{ old('surat_jalan_part_id'.$lastIdx) }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="part_no_{{ $lastSjPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                            <input type="hidden" id="part_id_{{ $lastSjPartCounter }}" name="part_id_{{ $lastSjPartCounter }}"
                                                                value="{{ $parts->part_id }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="part_name_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="qty_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                            <input type="hidden" id="qty_do_{{ $lastSjPartCounter }}" name="qty_do_{{ $lastSjPartCounter }}" value="{{ $parts->qty }}">
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <input type="text" id="qty_retur{{ $lastSjPartCounter }}" name="qty_retur{{ $lastSjPartCounter }}" style="text-align:right;"
                                                                class="form-control @error('qty_retur'.$lastSjPartCounter) is-invalid @enderror" maxlength="12"
                                                                value="@if (old('qty_retur'.$lastSjPartCounter)){{ old('qty_retur'.$lastSjPartCounter) }}@endif"
                                                                onchange="countTotal(this.value,{{ $lastSjPartCounter }});">
                                                            @error('qty_retur'.$lastSjPartCounter)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="unit_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->part_unit }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="price_{{ $lastSjPartCounter }}" class="col-form-label">{{ number_format($parts->price,0,'.',',') }}</label>
                                                            <input type="hidden" id="price_ori_{{ $lastSjPartCounter }}" name="price_ori_{{ $lastSjPartCounter }}"
                                                                value="{{ $parts->price }}">
                                                        </td>
                                                        <td scope="row" style="text-align:right;">
                                                            <label for="" id="total_{{ $lastSjPartCounter }}" class="col-form-label">{{ number_format(($parts->price*old('qty_retur'.$lastSjPartCounter)),0,'.',',') }}</label>
                                                            <input type="hidden" id="total_ori_{{ $lastSjPartCounter }}" name="total_ori_{{ $lastSjPartCounter }}"
                                                                value="{{ $parts->price*old('qty_retur'.$lastSjPartCounter) }}">
                                                        </td>
                                                        <td scope="row" style="text-align:left;">
                                                            <label for="" id="sj_no_linktopart_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->surat_jalan_no }}</label>
                                                        </td>
                                                        <td scope="row" style="text-align:center;vertical-align: middle;">
                                                            <input type="checkbox" id="rowCheck{{ $lastSjPartCounter }}" value="{{ $lastSjPartCounter }}">
                                                        </td>
                                                    @endif
                                                </tr>
                                                @php
                                                    $iRowPart++;
                                                @endphp
                                            @endif
                                            @php
                                                $lastSjPartCounter++;
                                            @endphp
                                        @endfor
                                    @else
                                        @foreach ($qNotaReturPart as $qNRpart)
                                            <tr id="rowSJdetail{{ $lastIdx }}">
                                                @php
                                                    $parts = \App\Models\Tx_surat_jalan_part::leftJoin('mst_parts AS mp','tx_surat_jalan_parts.part_id','=','mp.id')
                                                    ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
                                                    ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
                                                    ->leftJoin('tx_surat_jalans AS tx_so','tx_surat_jalan_parts.surat_jalan_id','=','tx_so.id')
                                                    ->select(
                                                        'tx_surat_jalan_parts.id AS surat_jalan_part_id',
                                                        'tx_surat_jalan_parts.surat_jalan_id AS surat_jalan_id',
                                                        'tx_surat_jalan_parts.part_id',
                                                        'tx_surat_jalan_parts.part_no',
                                                        'tx_surat_jalan_parts.qty',
                                                        'tx_surat_jalan_parts.price',
                                                        'mp.part_name',
                                                        'mg01.string_val AS part_unit',
                                                        'mp.weight',
                                                        'mg02.string_val AS weight_unit',
                                                        'tx_so.surat_jalan_no',
                                                    )
                                                    ->where([
                                                        'tx_surat_jalan_parts.id' => $qNRpart->surat_jalan_part_id,
                                                        'tx_surat_jalan_parts.active' => 'Y'
                                                    ])
                                                    ->first();
                                                @endphp
                                                @if ($parts)
                                                    @php
                                                        $totalPrice += ($qNRpart->final_price*$qNRpart->qty_retur);

                                                        $partNumber = $parts->part_no;
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                    @endphp
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="row_no_{{ $lastSjPartCounter }}" class="col-form-label">{{ $lastSjPartCounter+1 }}.</label>
                                                        <input type="hidden" id="sj_id_linktopart_{{ $lastSjPartCounter }}" name="sj_id_linktopart_{{ $lastSjPartCounter }}"
                                                            value="{{ $parts->surat_jalan_id }}">
                                                        <input type="hidden" id="surat_jalan_part_id{{ $lastSjPartCounter }}" name="surat_jalan_part_id{{ $lastSjPartCounter }}"
                                                            value="{{ $qNRpart->surat_jalan_part_id }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="part_no_{{ $lastSjPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                        <input type="hidden" id="part_id_{{ $lastSjPartCounter }}" name="part_id_{{ $lastSjPartCounter }}"
                                                            value="{{ $parts->part_id }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="part_name_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="qty_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                        <input type="hidden" id="qty_do_{{ $lastSjPartCounter }}" name="qty_do_{{ $lastSjPartCounter }}" value="{{ $parts->qty }}">
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <input type="text" id="qty_retur{{ $lastSjPartCounter }}" name="qty_retur{{ $lastSjPartCounter }}" style="text-align:right;"
                                                            class="form-control @error('qty_retur'.$lastSjPartCounter) is-invalid @enderror" maxlength="12"
                                                            value="{{ $qNRpart->qty_retur }}"
                                                            onchange="countTotal(this.value,{{ $lastSjPartCounter }});">
                                                        @error('qty_retur'.$lastSjPartCounter)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="unit_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->part_unit }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="price_{{ $lastSjPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price,0,'.',',') }}</label>
                                                        <input type="hidden" id="price_ori_{{ $lastSjPartCounter }}" name="price_ori_{{ $lastSjPartCounter }}"
                                                            value="{{ $parts->price }}">
                                                    </td>
                                                    <td scope="row" style="text-align:right;">
                                                        <label for="" id="total_{{ $lastSjPartCounter }}" class="col-form-label">{{ number_format($qNRpart->final_price*$qNRpart->qty_retur,0,'.',',') }}</label>
                                                        <input type="hidden" id="total_ori_{{ $lastSjPartCounter }}" name="total_ori_{{ $lastSjPartCounter }}" value="{{ $parts->price*$qNRpart->qty_retur }}">
                                                    </td>
                                                    <td scope="row" style="text-align:left;">
                                                        <label for="" id="sj_no_linktopart_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->surat_jalan_no }}</label>
                                                    </td>
                                                    <td scope="row" style="text-align:center;vertical-align: middle;">
                                                        <input type="checkbox" id="rowCheck{{ $lastSjPartCounter }}" value="{{ $lastSjPartCounter }}">
                                                    </td>
                                                @endif
                                            </tr>
                                            @php
                                                $lastSjPartCounter++;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" style="text-align: right;">Grand Total</td>
                                        <td style="text-align: right;">
                                            <label for="" id="total_before_vat">{{ $qCurrency->string_val.number_format($totalPrice,0,'.',',') }}</label>
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

    function dispNPandSJbyCust(custId){
        if(custId!=='#'){
            $("#new-row-so").empty();
            $("#new-row-sj-part").empty();
            $("#nota_penjualan_id").empty();
            $("#nota_penjualan_id").append(`<option value="#">Choose...</option>`);
            $("#surat_jalan_id").empty();
            $("#surat_jalan_id").append(`<option value="#">Choose...</option>`);

            var fd = new FormData();
            fd.append('cust_id', custId);
            $.ajax({
                url: "{{ url('/disp_np_sj_by_cust') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].notapenjualans;
                    let totFK = o.length;
                    if (totFK > 0) {
                        for (let i = 0; i < totFK; i++) {
                            optionText = o[i].delivery_order_no;
                            optionValue = o[i].id;
                            $("#nota_penjualan_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        }
    }

    function dispSJbyCust(np_id){
        if(np_id!=='#'){
            $("#surat_jalan_id").empty();
            $("#surat_jalan_id").append(`<option value="#">Choose...</option>`);

            var fd = new FormData();
            fd.append('np_id', np_id);
            $.ajax({
                url: "{{ url('/disp_sj_by_cust') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let s = res[0].suratjalans;
                    let totSO = s.length;
                    if (totSO > 0) {
                        for (let i = 0; i < totSO; i++) {
                            optionText = s[i].surat_jalan_no;
                            optionValue = s[i].id;
                            $("#surat_jalan_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        }
    }

    function addrowSJ(sj_id,sj_no){
        let lastCounter = $("#totRowSJ").val();
        var fd = new FormData();
        fd.append('sj_id', sj_id);
        $.ajax({
            url: "{{ url('/disp_sj_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let s = res[0].suratjalans;
                let custDocNo = '-';
                $("#branch_id").val(s.branch_id);
                if (s.customer_doc_no!=null){
                    custDocNo = s.customer_doc_no;
                }

                vHtml = '<tr id="rowSJ'+lastCounter+'">'+
                    '<td scope="row" style="text-align:right;"><label for="" id="surat-jalan-no'+lastCounter+'" class="col-form-label">'+(parseInt(lastCounter)+1)+'.</label></td>'+
                    '<td>'+
                    '<label for="" name="sj_no_'+lastCounter+'" id="sj_no_'+lastCounter+'" class="col-form-label">'+sj_no+'</label>'+
                    '<input type="hidden" name="sj_id_'+lastCounter+'" id="sj_id'+lastCounter+'" value="'+sj_id+'">'+
                    '</td>'+
                    '<td>'+
                    '<label for="" name="cust_doc_no_'+lastCounter+'" id="cust_doc_no_'+lastCounter+'" class="col-form-label">'+custDocNo+'</label>'+
                    '</td>'+
                    '<td style="text-align: center;vertical-align: middle;"><input type="checkbox" id="rowSJCheck'+lastCounter+'" value="'+lastCounter+'"></td>'+
                    '</tr>';
                $("#new-row-so").append(vHtml);

                $("#totRowSJ").val(parseInt(lastCounter)+1);

                // reset penomoran
                j = 1;
                for (i = 0; i < $("#totRowSJ").val(); i++) {
                    if($("#surat-jalan-no"+i).text()){
                        $("#surat-jalan-no"+i).text(j+'. ');
                        j++;
                    }
                }
                // reset penomoran - end
            },
        });

        let allSJ = $("#all_selected_SJ").val()+','+sj_no;
        $("#all_selected_SJ").val(allSJ);

        // prepare SO detail
        var fd = new FormData();
        fd.append('order_id', sj_id);
        $.ajax({
            url: "{{ url('/disp_sj_part') }}",
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
                let lastSjPartCounter = $("#totalRow").val();
                if (totPart > 0) {
                    for (let i = 0; i < totPart; i++) {
                        let partNumber = o[i].part_no;
                        if(partNumber.length<11){
                            partNumber = partNumber.substring(0, 5)+'-'+partNumber.substring(5, partNumber.length);
                        }else{
                            partNumber = partNumber.substring(0, 5)+'-'+partNumber.substring(5, 5)+'-'+partNumber.substring(10, partNumber.length);
                        }
                        vHtml = '<tr id="rowSJdetail'+lastSjPartCounter+'">'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="row_no_'+lastSjPartCounter+'" class="col-form-label">'+(parseInt(lastSjPartCounter)+1)+'.</label>'+
                            '<input type="hidden" id="sj_id_linktopart_'+lastSjPartCounter+'" name="sj_id_linktopart_'+lastSjPartCounter+'" value="'+sj_id+'">'+
                            '<input type="hidden" id="surat_jalan_part_id'+lastSjPartCounter+'" name="surat_jalan_part_id'+lastSjPartCounter+'" value="'+o[i].surat_jalan_part_id+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="part_no_'+lastSjPartCounter+'" class="col-form-label">'+partNumber+'</label>'+
                            '<input type="hidden" id="part_id_'+lastSjPartCounter+'" name="part_id_'+lastSjPartCounter+'" value="'+o[i].part_id+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="part_name_'+lastSjPartCounter+'" class="col-form-label">'+o[i].part_name+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="qty_'+lastSjPartCounter+'" class="col-form-label">'+o[i].qty+'</label>'+
                            '<input type="hidden" id="qty_do_'+lastSjPartCounter+'" name="qty_do_'+lastSjPartCounter+'" value="'+o[i].qty+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<input type="text" id="qty_retur'+lastSjPartCounter+'" name="qty_retur'+lastSjPartCounter+'" style="text-align:right;" class="form-control" '+
                            'maxlength="12" value="" onchange="countTotal(this.value,'+lastSjPartCounter+');">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="unit_'+lastSjPartCounter+'" class="col-form-label">'+o[i].part_unit+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="price_'+lastSjPartCounter+'" class="col-form-label">'+(parseFloat(o[i].price)).numberFormat(0,'.',',')+'</label>'+
                            '<input type="hidden" id="price_ori_'+lastSjPartCounter+'" name="price_ori_'+lastSjPartCounter+'" value="'+o[i].price+'">'+
                            '</td>'+
                            '<td scope="row" style="text-align:right;">'+
                            '<label for="" id="total_'+lastSjPartCounter+'" class="col-form-label"></label>'+
                            '<input type="hidden" id="total_ori_'+lastSjPartCounter+'" name="total_ori_'+lastSjPartCounter+'" value="0">'+
                            '</td>'+
                            '<td scope="row" style="text-align:left;">'+
                            '<label for="" id="sj_no_linktopart_'+lastSjPartCounter+'" class="col-form-label">'+sj_no+'</label>'+
                            '</td>'+
                            '<td scope="row" style="text-align:center;vertical-align: middle;"><input type="checkbox" id="rowCheck'+lastSjPartCounter+'" value="'+lastSjPartCounter+'"></td>'+
                            '</tr>';
                        $("#new-row-sj-part").append(vHtml);
                        lastSjPartCounter=parseInt(lastSjPartCounter)+1;
                    }

                    $("#totalRow").val(lastSjPartCounter);
                    countGrandTotal();

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
                    $('#submit-form').attr('action', "{{ url('/del_notareturnontax') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#rowSJdetail"+i).remove();
                }
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowSJ").val(); i++) {
                if($("#surat-jalan-no"+i).text()){
                    $("#surat-jalan-no"+i).text(j+'. ');
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

            let totalPrice = 0;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if (!isNaN($("#total_ori_"+i).val())){
                    totalPrice = parseFloat(totalPrice)+parseFloat($("#total_ori_"+i).val());
                }
            }
            if(isNaN(totalPrice)){
                $("#total_before_vat").text('{{ $qCurrency->string_val }}0');
            }else{
                $("#total_before_vat").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice)).numberFormat(0,'.',','));
            }
        });

        $("#gen-part").click(function() {            
            let sj_id = $("#surat_jalan_id option:selected").val();
            let sj_no = $("#surat_jalan_id option:selected").text();
            for(let i=0;i<$("#totRowSJ").val();i++){
                if($("#sj_no_"+i).text()===sj_no){
                    alert('The SJ number already exists.');
                    return false;
                }
            }
            if(sj_id!=='#'){
                addrowSJ(sj_id,sj_no);
            }
        });

        $("#del-row-sj").click(function() {
            let SJno2Del = '';
            for (i=0;i<$("#totRowSJ").val(); i++) {
                if ($("#rowSJCheck"+i).is(':checked')) {
                    SJno2Del = $("#sj_no_"+i).text();
                    $("#rowSJ"+i).remove();
                    $("#all_selected_SJ").val($("#all_selected_SJ").val().replaceAll(','+SJno2Del,''));

                    for (j=0; j<$("#totalRow").val(); j++) {
                        if($("#sj_no_linktopart_"+j).text()===SJno2Del){
                            $("#rowSJdetail"+j).remove();
                        }
                    }
                }
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totRowSJ").val(); i++) {
                if($("#surat-jalan-no"+i).text()){
                    $("#surat-jalan-no"+i).text(j+'. ');
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

            let totalPrice = 0;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if (!isNaN($("#total_ori_"+i).val())){
                    totalPrice = parseFloat(totalPrice)+parseFloat($("#total_ori_"+i).val());
                }
            }
            if(isNaN(totalPrice)){
                $("#total_before_vat").text('{{ $qCurrency->string_val }}0');
            }else{
                $("#total_before_vat").text('{{ $qCurrency->string_val }}'+(parseFloat(totalPrice)).numberFormat(0,'.',','));
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
