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
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                {{-- @if($errors->any())
                Error:
                {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                @endif --}}
                <div class="row mb-3">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Order No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $orders->purchase_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Order Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->purchase_date), 'd/m/Y') }}</label>
                                </div>
                                <div id="supplier_data" class="row mb-3">
                                    <label for="supplier_data" class="col-sm-3 col-form-label">Information</label>
                                    <div id="supplier_info" class="col-sm-9">
                                        @isset($supplierPics[0])
                                            {!!
                                            (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind:'').' '.$supplierPics[0]->name.
                                            '<br />Address: '.$supplierPics[0]->office_address.
                                            ($supplierPics[0]->subdistrict->sub_district_name=='Other'?'':
                                            ', '.ucwords(strtolower($supplierPics[0]->subdistrict->sub_district_name))).
                                            ($supplierPics[0]->district->district_name=='Other'?'':
                                            ', '.$supplierPics[0]->district->district_name).
                                            ($supplierPics[0]->city->city_name=='Other'?'':
                                            '<br />'.($supplierPics[0]->city->city_type=='Luar Negeri'?'':$supplierPics[0]->city->city_type).' '.
                                            $supplierPics[0]->city->city_name).
                                            ($supplierPics[0]->province->province_name=='Other'?'':
                                            '<br />'.$supplierPics[0]->province->province_name).
                                            '<br />'.$supplierPics[0]->country->country_name.
                                            ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                            ' '.$supplierPics[0]->subdistrict->post_code)
                                            !!}
                                        @endisset
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">PQ No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ (is_null($orders->quotation)?'---':$orders->quotation->quotation_no) }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">PIC</label>
                                    <label for="" class="col-sm-9 col-form-label">
                                        @if ($orders->pic_idx==1)
                                            {{ !is_null($orders->supplier)?$orders->supplier->pic1_name:'' }}
                                        @else
                                            {{ !is_null($orders->supplier)?$orders->supplier->pic2_name:'' }}
                                        @endif
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Currency</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ !is_null($orders->currency)?$orders->currency->title_ind:'' }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Ship To</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $orders->branch->name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Ship By</label>
                                    <label for="" class="col-sm-9 col-form-label">
                                        @switch($orders->courier_type)
                                            @case(env('AMBIL_SENDIRI'))
                                                {{ env('AMBIL_SENDIRI_STR') }}
                                                @break

                                            @case(env('DIANTAR'))
                                                {{ env('DIANTAR_STR') }}
                                                @break

                                            @case(env('COURIER'))
                                                {{ env('COURIER_STR').(!is_null($orders->courier)?' - '.$orders->courier->name:'') }}
                                                @break

                                            @default
                                                {{ '' }}
                                        @endswitch
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Estimated Supply</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ !is_null($orders->est_supply_date)?date_format(date_create($orders->est_supply_date), 'd/m/Y'):'' }}</label>
                                </div>
                                @if ($orders->supplier->supplier_type_id==11)
                                    <div class="row mb-3">
                                        <label for="" class="col-sm-3 col-form-label">VAT</label>
                                        <label for="" class="col-sm-9 col-form-label">{{ $orders->is_vat }}</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Status</label>
                                    <label for="" class="col-sm-9 col-form-label">
                                        @php
                                            $isReceived = false;
                                            $qRO = \App\Models\Tx_receipt_order::where('po_or_pm_no','LIKE','%'.$orders->purchase_no.'%')
                                            ->where('active','=','Y')
                                            ->first();
                                        @endphp
                                        @if ($qRO)
                                            @php
                                                $isReceived = true;
                                            @endphp
                                        @endif
                                        @if ($orders->active=='Y' && is_null($orders->approved_by) && is_null($orders->canceled_by) && !$isReceived)
                                            {{ 'Waiting for Approval' }}
                                        @endif
                                        @if ($orders->active=='Y' && !is_null($orders->approved_by) && !$isReceived)
                                            {{ 'Approved' }}
                                        @endif
                                        @if ($orders->active=='Y' && !is_null($orders->canceled_by))
                                            {{ 'Rejected' }}
                                        @endif
                                        @if ($orders->active=='Y' && !is_null($orders->approved_by) && $isReceived)
                                            {{ 'Received' }}
                                        @endif
                                        @if ($orders->active=='N')
                                            {{ 'Canceled' }}
                                        @endif
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Reason</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $orders->rejected_reason }}</label>
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
                        <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr style="width: 100%;">
                                    <th scope="col" style="width: 2%;text-align:center;">#</th>
                                    <th scope="col" style="width: 25%;">Part</th>
                                    <th scope="col" style="width: 7%;">Qty</th>
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
                                    <th id="price-rp" scope="col" style="width: 18%;">{{ $price_rp }}</th>
                                    <th id="price-rp-total" style="width: 10%;">{{ $price_rp_total }}</th>
                                    <th scope="col" style="width: 15%;">Description</th>
                                    <th scope="col" style="width: 16%;">Final&nbsp;Cost/FOB</th>
                                    <th scope="col" style="width: 6%;">OH</th>
                                    <th scope="col" style="width: 6%;">OO</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $i=0;
                                @endphp
                                    @foreach($orderParts AS $mp)
                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;">
                                            <label for="" class="col-form-label">{{ $i + 1 }}.</label>
                                            <input type="hidden" name="order_part_id_{{ $i }}" id="order_part_id_{{ $i }}" value="{{ $mp->id }}">
                                        </th>
                                        @php
                                            $q = \App\Models\Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                                            ->leftJoin('mst_globals as q_type', 'mst_parts.quantity_type_id', '=', 'q_type.id')
                                            ->select(
                                                'mst_parts.*',
                                                'tx_qty_parts.qty as total_qty',
                                                'q_type.title_ind AS quantity_type',
                                            )
                                            ->where([
                                                'mst_parts.id' => $mp->part_id,
                                                'tx_qty_parts.branch_id' => $userLogin->branch_id
                                            ])
                                            ->first();

                                            $qOhOo = \App\Models\Tx_purchase_order_oo_oh_part::where([
                                                'purchase_order_id' => $mp->order_id,
                                                'purchase_order_part_id' => $mp->id,
                                                'part_id' => $mp->part_id,
                                                'branch_id' => $userLogin->branch_id
                                            ])
                                            ->first();
                                        @endphp
                                        <td>
                                            @php
                                                $partNumber = $mp->part->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <label for="" class="col-form-label">{{ $partNumber.' : '.$mp->part->part_name }}</label>
                                        </td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($mp->qty,0,'.',',') }}</label></td>
                                        <td><label id="unit-{{ $i }}" for="" class="col-form-label">{{ (!is_null($q)?$q->quantity_type:'') }}</label></td>
                                        <td style="text-align: right;">
                                            <label for="" class="col-form-label">{{ (!is_null($orders->currency)?$orders->currency->string_val:'').number_format($mp->price,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" class="col-form-label">{{ (!is_null($orders->currency)?$orders->currency->string_val:'').number_format($mp->price*$mp->qty,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                        </td>
                                        <td>
                                            <label for="" class="col-form-label">{{ $mp->description }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($q)?'':$qCurrency->string_val.number_format($q->final_cost,0,'.',',').' / '.(is_null($q->fobCurr)?'':$q->fobCurr->string_val).number_format($q->final_fob,2,'.',',')) }}</label>
                                        </td>
                                        <td><label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format((!is_null($qOhOo)?($qOhOo->last_OH_PO_created>0?$qOhOo->last_OH_PO_created:0):0),0,'.',',') }}</label></td>
                                        <td><label id="oo-{{ $i }}" for="" class="col-form-label">{{ number_format((!is_null($qOhOo)?$qOhOo->last_OO_PO_created:0),0,'.',',') }}</label></td>
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td colspan="5" style="text-align: right;">
                                        <label for="" class="col-form-label">Total</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" class="col-form-label">{{ $orders->currency->string_val.number_format($orders->total_before_vat,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                    </td>
                                    <td colspan="4">&nbsp;</td>
                                </tr>
                                @if ($orders->supplier->supplier_type_id==11)
                                    @if ($orders->is_vat=='Y')
                                        <tr>
                                            <td colspan="5" style="text-align: right;">
                                                <label for="" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($orders->is_vat=='Y')
                                                    @php
                                                        $vat = $orders->total_after_vat-$orders->total_before_vat;
                                                    @endphp
                                                    <label for="" class="col-form-label">{{ $orders->currency->string_val.number_format($vat,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                                @else
                                                    <label for="" class="col-form-label"></label>
                                                @endif
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" style="text-align: right;">
                                                <label for="" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($orders->is_vat=='Y')
                                                    <label for="" class="col-form-label">{{ $orders->currency->string_val.number_format($orders->total_after_vat,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                                @else
                                                    <label for="" class="col-form-label">{{ $orders->currency->string_val.number_format($orders->total_before_vat,($orders->supplier->supplier_type_id==10?2:0),'.',',') }}</label>
                                                @endif
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    $(document).ready(function() {
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
