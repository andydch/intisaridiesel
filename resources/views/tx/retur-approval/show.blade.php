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
            @if (session('status-error'))
                <div class="alert alert-danger">
                    {{ session('status-error') }}
                </div>
            @endif
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($qNotaRetur->nota_retur_no)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
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
                                                <label for="" class="col-sm-3 col-form-label">NP No*</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->delivery_order->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="all-fk" class="col-sm-3 col-form-label">SJ No*</label>
                                                <div class="col-sm-9">
                                                    <table id="fk-tables" class="table table-bordered mb-0">
                                                        <thead>
                                                            <tr style="width: 100%;">
                                                                <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                <th scope="col" style="width: 47%;text-align:center;">SJ No</th>
                                                                <th scope="col" style="width: 50%;text-align:center;">Cust Doc No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="new-row-so">
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
                                                                        <td scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $iRow }}.</label></td>
                                                                        <td>
                                                                            <label for="" name="sj_no_{{ $lastCounter }}" id="sj_no_{{ $lastCounter }}"
                                                                                class="col-form-label">{{ $all_selected_SJ[$lastCounter] }}</label>
                                                                            <input type="hidden" name="sj_id_{{ $lastCounter }}" id="sj_id_{{ $lastCounter }}" value="{{ $qSO->id }}">
                                                                        </td>
                                                                        <td>
                                                                            <label for="" name="cust_doc_no_{{ $lastCounter }}" id="cust_doc_no_{{ $lastCounter }}"
                                                                                class="col-form-label">{{ $qSO->customer_doc_no }}</label>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->remark }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created by</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qNotaRetur->createdBy->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Approval Status</label>
                                                <label for="" class="col-sm-9 col-form-label">
                                                    @if(!is_null($qNotaRetur->approved_by) && $qNotaRetur->active=='Y')
                                                        {{ 'Approved at '.
                                                            date_format(date_add(date_create($qNotaRetur->approved_at), 
                                                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                                                            ' by '.$qNotaRetur->approvedBy->name }}
                                                    @endif
                                                    @if(!is_null($qNotaRetur->canceled_by) && $qNotaRetur->active=='Y')
                                                        {{ 'Rejected at '.
                                                            date_format(date_add(date_create($qNotaRetur->canceled_at), 
                                                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                                                            $qNotaRetur->canceledBy->name }}
                                                    @endif
                                                    @if(is_null($qNotaRetur->approved_by) && is_null($qNotaRetur->canceled_by) && $qNotaRetur->active=='Y' && strpos($qNotaRetur->nota_retur_no,'Draft')==0)
                                                        {{ 'Waiting for Approval' }}
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-3">
                                                    <select class="form-select single-select @error('order_appr') is-invalid @enderror" id="order_appr" name="order_appr">
                                                        <option value="A">Approve</option>
                                                        {{-- <option value="R">Reject</option> --}}
                                                    </select>
                                                    @error('order_appr')
                                                        <div class="invalid-feedback">{!! $message !!}</div>
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
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 15%;">Part No</th>
                                        <th scope="col" style="width: 16%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 5%;">Qty Retur</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 11%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 11%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 5%;">SO No</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row-sj-part">
                                    @php
                                        $lastIdx = 0;
                                        $totalPrice = 0;
                                        $lastSjPartCounter=0;
                                    @endphp
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
                                                </td>
                                                <td scope="row" style="text-align:left;">
                                                    <label for="" id="part_no_{{ $lastSjPartCounter }}" class="col-form-label">{{ $partNumber }}</label>
                                                </td>
                                                <td scope="row" style="text-align:left;">
                                                    <label for="" id="part_name_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->part_name }}</label>
                                                </td>
                                                <td scope="row" style="text-align:right;">
                                                    <label for="" id="qty_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->qty }}</label>
                                                    <input type="hidden" id="qty_do_{{ $lastSjPartCounter }}" name="qty_do_{{ $lastSjPartCounter }}" value="{{ $parts->qty }}">
                                                </td>
                                                <td scope="row" style="text-align:right;">
                                                    <label for="" id="qty_retur{{ $lastSjPartCounter }}" class="col-form-label">{{ $qNRpart->qty_retur }}</label>
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
                                                </td>
                                                <td scope="row" style="text-align:left;">
                                                    <label for="" id="sj_no_linktopart_{{ $lastSjPartCounter }}" class="col-form-label">{{ $parts->surat_jalan_no }}</label>
                                                </td>
                                            @endif
                                        </tr>
                                        @php
                                            $lastSjPartCounter++;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align: right;">Grand Total</td>
                                        <td style="text-align: right;">
                                            <label for="" id="total_before_vat">{{ $qCurrency->string_val.number_format($totalPrice,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save-approval-status-btn" class="btn btn-primary px-5" value="Approve">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
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
        $("#save-approval-status-btn").click(function() {
            if(!confirm("The approval status will be changed, after this it cannot be undone!\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
