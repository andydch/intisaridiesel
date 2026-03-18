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
            @if (session('status-error'))
                <div class="alert alert-danger">
                    {{ session('status-error') }}
                </div>
            @endif
            <form id="submitApproval" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$orders->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                @php
                                    $disabled = '';
                                @endphp
                                @if (!is_null($orders->approved_status))
                                    @php
                                        $disabled = 'disabled';
                                    @endphp
                                @endif
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">PO No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $orders->purchase_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">PQ No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ !is_null($orders->quotation)?$orders->quotation->quotation_no:'' }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->purchase_date), 'd/m/Y') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Supplier</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ !is_null($orders->supplier)?$orders->supplier->name:''}}</label>
                                </div>
                                <div id="supplier_data" class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Information</label>
                                    <label id="supplier_info" for="" class="col-sm-9 col-form-label">
                                        @isset($supplierPics[0])
                                            {!!
                                            (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind:'').' '.$supplierPics[0]->name.
                                            '<br />Supplier Type: '.$supplierPics[0]->supplier_type->title_ind.
                                            '<br />Address: '.$supplierPics[0]->office_address.
                                            ($supplierPics[0]->subdistrict->sub_district_name=='Other'?'':
                                            ', '.ucwords(strtolower($supplierPics[0]->subdistrict->sub_district_name))).
                                            ($supplierPics[0]->district->district_name=='Other'?'':
                                            ', '.$supplierPics[0]->district->district_name).
                                            ($supplierPics[0]->city->city_name=='Other'?'':
                                            '<br />'.($supplierPics[0]->city->city_type=='Luar
                                            Negeri'?'':$supplierPics[0]->city->city_type).' '.
                                            $supplierPics[0]->city->city_name).
                                            ($supplierPics[0]->province->province_name=='Other'?'':
                                            '<br />'.$supplierPics[0]->province->province_name).
                                            '<br />'.$supplierPics[0]->country->country_name.
                                            ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                            ' '.$supplierPics[0]->subdistrict->post_code)
                                            !!}
                                        @endisset
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Supplier PIC</label>
                                    <label for="" class="col-sm-9 col-form-label">
                                        @if ($orders->pic_idx==1)
                                            {{ !is_null($orders->supplier)?$orders->supplier->pic1_name:'' }}
                                        @else
                                            {{ !is_null($orders->supplier)?$orders->supplier->pic2_name:'' }}
                                        @endif
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="currency_id" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                        {{ !is_null($orders->currency)?$orders->currency->title_ind:'' }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                    <div class="col-sm-9">
                                        {{ $orders->branch->name }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="active" class="col-sm-3 col-form-label">Approval Status</label>
                                    <div class="col-sm-9">
                                        @if (is_null($orders->approved_status))
                                            {{ 'Waiting for approval' }}
                                        @else
                                            @if ($orders->approved_status=='A')
                                                {{ 'Approved' }}
                                            @else
                                                {{ 'Rejected' }}
                                            @endif
                                            @if (!is_null($orders->approved_by_info))
                                                {!! ' by '.$orders->approved_by_info->name.' at '.
                                                    date_format(date_add(date_create($orders->approved_at),
                                                    date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd M Y H:i:s') !!}
                                            @endif
                                        @endif
                                        <br /><br />
                                        <span style="font-weight: bold;">Notes:&nbsp;</span>{{ $orders->rejected_reason
                                        }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="active" class="col-sm-3 col-form-label">&nbsp;</label>
                                    <div class="col-sm-9">
                                        <select {{ $disabled }}
                                            class="form-select single-select @error('order_appr') is-invalid @enderror" id="order_appr" name="order_appr">
                                            @if ($orders->approved_status=='R' || is_null($orders->approved_status))
                                                <option @if (old('order_appr')=='A'){{'selected'}}@endif value="A">Approve</option>
                                            @endif
                                            {{-- @if ($orders->approved_status=='A' || is_null($orders->approved_status))
                                                <option @if (old('order_appr')=='R'){{'selected'}}@endif value="R">Reject</option>
                                            @endif --}}
                                        </select><br />
                                        Reason:<br />
                                        <textarea {{ $disabled }} id="reason" name="reason" maxlength="2048"
                                            class="form-control @error('reason') is-invalid @enderror" rows="3"
                                            aria-label="reason">@if (old('reason')){{ old('reason') }}@endif</textarea>
                                        @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input {{ $disabled }} type="submit" id="complexConfirm" class="btn btn-primary px-5"
                                            style="margin-top: 15px;" value="Submit">
                                    </div>
                                </div> --}}
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
                                        <th scope="col" style="width: 5%;text-align:center;">#</th>
                                        <th scope="col" style="width: 10%;">Part No</th>
                                        <th scope="col" style="width: 15%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Price @if ($orders->supplier->supplier_type_id==11)({{ $qCurrency->string_val }})@endif</th>
                                        <th scope="col" style="width: 10%;">Total @if ($orders->supplier->supplier_type_id==11)({{ $qCurrency->string_val }})@endif</th>
                                        <th scope="col" style="width: 15%;">Description</th>
                                        <th scope="col" style="width: 15%;">Final Cost / Final FOB</th>
                                        <th scope="col" style="width: 10%;">Final Price</th>
                                        <th scope="col" style="width: 5%;">OH</th>
                                        <th scope="col" style="width: 5%;">SO</th>
                                        <th scope="col" style="width: 5%;">OO</th>
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
                                                @endphp
                                            </th>
                                            <td>
                                                @php
                                                    $partNumber = $mp->part->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                <label for="" class="col-form-label">{{ $partNumber }}</label>
                                            </td>
                                            <td>
                                                <label for="" class="col-form-label">{{ $mp->part->part_name }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" class="col-form-label">{{ $mp->qty }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($mp->price,2,'.',',') }}@else{{ number_format($mp->price,0,'.',',') }}@endif</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($mp->price*$mp->qty,2,'.',',') }}@else{{ number_format($mp->price*$mp->qty,0,'.',',') }}@endif</label>
                                            </td>
                                            <td>
                                                <label for="" class="col-form-label">{{ $mp->description }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (!isset($q)?'':number_format($q->final_cost,0,'.',',').' / '.(is_null($q->fobCurr)?'':$q->fobCurr->string_val).number_format($q->final_fob,0,'.',',')) }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label id="final-price-{{ $i }}" for="" class="col-form-label">{{ number_format($q->final_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format($q->total_qty,0,'.',',') }}</label>
                                                <input type="hidden" name="oh_{{ $i }}_tmp" id="oh_{{ $i }}_tmp" value="{{ $q->total_qty }}">
                                            </td>
                                            @php
                                                $tot_so = 0;
                                                $so = \App\Models\Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0) AS tot_so')
                                                ->leftJoin('tx_sales_orders AS tx_so', 'tx_sales_order_parts.order_id', '=', 'tx_so.id')
                                                ->where('tx_so.sales_order_no', 'NOT LIKE', '%Draft%')
                                                ->whereNotIn('tx_so.id',function ($query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_orders as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                                                    ->where([
                                                        'tx_do_parts.active' => 'Y',
                                                        'tx_do.active' => 'Y',
                                                    ]);
                                                })
                                                ->where([
                                                    'tx_sales_order_parts.part_id' => $mp->part_id,
                                                    'tx_sales_order_parts.active' => 'Y',
                                                    'tx_so.branch_id' => $orders->branch_id,
                                                    'tx_so.need_approval' => 'N',
                                                    'tx_so.active' => 'Y',
                                                ])
                                                ->first();
                                                if ($so){$tot_so = $so->tot_so;}

                                                $tot_sj = 0;
                                                $sj = \App\Models\Tx_surat_jalan_part::selectRaw('IFNULL(SUM(tx_surat_jalan_parts.qty),0) AS tot_so')
                                                ->leftJoin('tx_surat_jalans AS tx_sj', 'tx_surat_jalan_parts.surat_jalan_id', '=', 'tx_sj.id')
                                                ->where('tx_sj.surat_jalan_no', 'NOT LIKE', '%Draft%')
                                                ->whereNotIn('tx_sj.id',function ($query) {
                                                    $query->select('tx_do_parts.sales_order_id')
                                                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                                                    ->where([
                                                        'tx_do_parts.active' => 'Y',
                                                        'tx_do.active' => 'Y',
                                                    ]);
                                                })
                                                ->where([
                                                    'tx_surat_jalan_parts.part_id' => $mp->part_id,
                                                    'tx_surat_jalan_parts.active' => 'Y',
                                                    'tx_sj.branch_id' => $orders->branch_id,
                                                    'tx_sj.need_approval' => 'N',
                                                    'tx_sj.active' => 'Y',
                                                ])
                                                ->first();
                                                if ($sj){$tot_sj = $sj->tot_so;}

                                                $so_now = $tot_so+$tot_sj;
                                            @endphp
                                            <td style="text-align: right;">
                                                <label id="so-{{ $i }}" for="" class="col-form-label">{{ number_format($so_now,0,'.',',') }}</label>
                                            </td>
                                            @php
                                                $tot_mo = 0;
                                                $oo_mo = \App\Models\Tx_purchase_memo_part::selectRaw('IFNULL(SUM(tx_purchase_memo_parts.qty),0) AS tot_mo')
                                                ->leftJoin('tx_purchase_memos AS tx_pm', 'tx_purchase_memo_parts.memo_id', '=', 'tx_pm.id')
                                                ->where('tx_pm.memo_no', 'NOT LIKE', '%Draft%')
                                                ->where([
                                                    'tx_purchase_memo_parts.part_id' => $mp->part_id,
                                                    'tx_purchase_memo_parts.active' => 'Y',
                                                    'tx_pm.branch_id' => $orders->branch_id,
                                                    'tx_pm.active' => 'Y',
                                                ])
                                                ->first();
                                                if ($oo_mo){$tot_mo = $oo_mo->tot_mo;}

                                                $tot_po = 0;
                                                $oo_po = \App\Models\Tx_purchase_order_part::selectRaw('IFNULL(SUM(tx_purchase_order_parts.qty),0) AS tot_po')
                                                ->leftJoin('tx_purchase_orders AS tx_po', 'tx_purchase_order_parts.order_id', '=', 'tx_po.id')
                                                ->where('tx_po.purchase_no', 'NOT LIKE', '%Draft%')
                                                ->whereRaw('tx_po.id<>'.$orders->id)
                                                ->whereRaw('tx_po.approved_by IS NOT NULL')
                                                ->where([
                                                    'tx_purchase_order_parts.part_id' => $mp->part_id,
                                                    'tx_purchase_order_parts.active' => 'Y',
                                                    'tx_po.branch_id' => $orders->branch_id,
                                                    'tx_po.active' => 'Y',
                                                ])
                                                ->first();
                                                if ($oo_po){$tot_po = $oo_po->tot_po;}

                                                $tot_ro = 0;
                                                $oo_ro = \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0) AS tot_ro')
                                                ->leftJoin('tx_receipt_orders AS tx_ro', 'tx_receipt_order_parts.receipt_order_id', '=', 'tx_ro.id')
                                                ->where('tx_receipt_order_parts.po_mo_no', '<>', $orders->purchase_no)
                                                ->where('tx_ro.receipt_no', 'NOT LIKE', '%Draft%')
                                                ->where([
                                                    'tx_receipt_order_parts.part_id' => $mp->part_id,
                                                    'tx_receipt_order_parts.active' => 'Y',
                                                    'tx_ro.branch_id' => $orders->branch_id,
                                                    'tx_ro.active' => 'Y',
                                                ])
                                                ->first();
                                                if ($oo_ro){$tot_ro = $oo_ro->tot_ro;}

                                                $oo = $tot_mo+$tot_po-$tot_ro;
                                            @endphp
                                            <td style="text-align: right;">
                                                <label id="oo-{{ $i }}" for="" class="col-form-label">{{ number_format($oo,0,'.',',') }}</label>
                                                <input type="hidden" name="oo_{{ $i }}_tmp" id="oo_{{ $i }}_tmp" value="{{ $oo }}">
                                            </td>
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
                                            <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($orders->total_before_vat,2,'.',',') }}@else{{ number_format($orders->total_before_vat,0,'.',',') }}@endif</label>
                                        </td>
                                        <td colspan="5">&nbsp;</td>
                                    </tr>
                                    @if ($orders->supplier->supplier_type_id==11)
                                        <tr>
                                            <td colspan="5" style="text-align: right;">
                                                <label for="" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                            @if ($orders->is_vat==='Y')
                                            @php
                                                $vat = $orders->total_after_vat-$orders->total_before_vat;
                                            @endphp
                                                <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($vat,2,'.',',') }}@else{{ number_format($vat,0,'.',',') }}@endif</label>
                                            @else
                                                <label for="" class="col-form-label">0,00</label>
                                            @endif
                                            </td>
                                            <td colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" style="text-align: right;">
                                                <label for="" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($orders->is_vat==='Y')
                                                    <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($orders->total_after_vat,2,'.',',') }}@else{{ number_format($orders->total_after_vat,0,'.',',') }}@endif</label>
                                                @else
                                                    <label for="" class="col-form-label">@if($orders->supplier->supplier_type_id==10){{ $orders->currency->string_val.number_format($orders->total_before_vat,2,'.',',') }}@else{{ number_format($orders->total_before_vat,0,'.',',') }}@endif</label>
                                                @endif
                                            </td>
                                            <td colspan="5"></td>
                                        </tr>
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
                                    <input {{ $disabled }} type="submit" id="complexConfirm" class="btn btn-primary px-5" value="Approve">
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
        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });

        $('#submitApproval').submit(function(event){
            if(!confirm("The approval status becomes "+ $("#order_appr option:selected").text() +", after this it cannot be changed!\nContinue?")){
                event.preventDefault();
            }
        });

        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
