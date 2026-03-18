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
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">PV No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qPaymentInv->payment_voucher_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">PS No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qPaymentInv->payment_voucher_plan_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Supplier</label>
                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($qPaymentInv->supplier)?$qPaymentInv->supplier->name:'' }}</label>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">NPWP</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->payment_type_id }}</label>
                            </div> --}}
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Metode Pembayaran</label>
                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($qPaymentInv->payment_mode)?$payment_mode_string[$qPaymentInv->payment_mode-1]:'') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Pembayaran Via</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @php
                                        $paymentRef = \App\Models\Mst_global::select(
                                            'title_ind',
                                            'title_eng',
                                            'slug',
                                        )
                                        ->where([
                                            'id'=>$qPaymentInv->payment_reference_id,
                                        ])
                                        ->first();
                                    @endphp
                                    @if ($paymentRef){{ $paymentRef->title_ind }}@endif
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">No Tagihan Supplier</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->tagihan_supplier?$qPaymentInv->tagihan_supplier->tagihan_supplier_no:'' }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">No Rekening</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @php
                                        $coaCashInBank = \App\Models\Mst_coa::select(
                                            'id',
                                            'coa_name'
                                        )
                                        ->where([
                                            'id' => $qPaymentInv->coa_id,
                                            'active' => 'Y'
                                        ])
                                        ->first();
                                    @endphp
                                    @if ($coaCashInBank){{ $coaCashInBank->coa_name }}@endif
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="reference_no" class="col-sm-3 col-form-label">Transaction / Giro No</label>
                                <label for="reference_no" class="col-sm-9 col-form-label">{{ $qPaymentInv->reference_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Transaction / Giro Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qPaymentInv->reference_date), 'd/m/Y') }}</label>
                            </div>
                            @php
                                $grandTotalTerbayar = $qPaymentInv->payment_total_after_vat+
                                    $qPaymentInv->admin_bank+
                                    $qPaymentInv->biaya_kirim+
                                    $qPaymentInv->biaya_lainnya+
                                    $qPaymentInv->biaya_asuransi-
                                    $qPaymentInv->diskon_pembelian;
                            @endphp
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Total Pembayaran</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($grandTotalTerbayar,0,".",",") }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Journal Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qPaymentInv->payment_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->remark }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
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
                                    <th scope="col" style="width: 15%;">Invoice No</th>
                                    <th scope="col" style="width: 10%;">Date</th>
                                    <th scope="col" style="width: 15%;">RO No - RE No</th>
                                    <th scope="col" style="width: 12%;">Description</th>
                                    <th scope="col" style="width: 13%;">Total ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 13%;">Terbayar ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 13%;">Sisa ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $ro_vat = 0;
                                @endphp
                                @if (old('totalRow'))
                                    {{-- empty --}}
                                @else
                                    @php
                                        $i = 0;
                                        $totTerbayar = 0;
                                        $ro_vat_total = 0;
                                    @endphp
                                    @foreach ($queryInv as $qI)
                                        <tr id="row{{ $i }}">
                                            <td scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label">{{ $i + 1 }}.</label>
                                                <input type="hidden" id="inv_id_{{ $i }}" name="inv_id_{{ $i }}" maxlength="25" value="{{ $qI->id }}" />
                                            </td>
                                            <td><label for="" class="col-form-label">{{ ($qI->receipt_order?$qI->receipt_order->invoice_no:'-') }}</label></td>
                                            @php
                                                $RoNo = '';
                                                $receipt_date_01 = '';
                                                $returnNo = '';
                                                $totEveryReturBeforeVat = '';
                                                $exchange_rate = 1;
                                                $exch_rate_for_vat = 1;
                                                $receipt_id = $qI->receipt_order_id;
                                                $total_before_vat = 0;
                                                $total_before_vat_ro = 0;
                                                $total_vat_ro = 0;
                                                $qRO = \App\Models\Tx_receipt_order::where('id','=',$receipt_id)
                                                ->first();
                                                if($qRO){
                                                    $RoNo = $qRO->receipt_no;
                                                    $total_before_vat_ro = $qRO->supplier_type_id==10?$qRO->total_before_vat_rp:$qRO->total_before_vat;
                                                    $total_vat_ro = $qRO->supplier_type_id==10?$qRO->total_vat_rp:$qRO->total_vat;
                                                    $receipt_date_01 = date_format(date_create($qRO->receipt_date),"d/m/Y");
                                                    $exchange_rate = (!is_null($qRO->exchange_rate) && $qRO->exchange_rate>0?$qRO->exchange_rate:1);
                                                    $exch_rate_for_vat = (!is_null($qRO->exc_rate_for_vat) && $qRO->exc_rate_for_vat>0?$qRO->exc_rate_for_vat:1);
                                                    $ro_vat = $qRO->vat_val;
                                                    $total_before_vat = $qRO->supplier_type_id==10?$qRO->total_before_vat_rp:$qRO->total_before_vat;
                                                }
                                                $qReturs = \App\Models\Tx_purchase_retur::where('receipt_order_id','=',$receipt_id)
                                                ->whereRaw('approved_by IS NOT NULL')
                                                ->get();
                                            @endphp
                                            <td>
                                                <label id="ro_date_{{ $i }}" class="col-form-label">{{ $receipt_date_01 }}</label>
                                            </td>
                                            <td>
                                                @foreach ($qReturs as $qR)
                                                    @php
                                                        $returnNo .= '<br/>'.$qR->purchase_retur_no;
                                                        $totEveryReturBeforeVat .= '('.number_format($qR->total_before_vat,0,".",",").')<br/>';
                                                    @endphp
                                                @endforeach
                                                <input type="hidden" id="receipt_id_{{ $i }}" name="receipt_id_{{ $i }}" value="{{ $receipt_id }}" />
                                                <label id="ro_and_retur_{{ $i }}" class="col-form-label">{!! $RoNo.$returnNo !!}</label>
                                            </td>
                                            <td><label for="" class="col-form-label">{{ $qI->description }}</label></td>
                                            <td style="text-align: right;">
                                                @php
                                                    $sumTotBeforeVat = \App\Models\Tx_purchase_retur::where('receipt_order_id','=',$receipt_id)
                                                    ->whereRaw('approved_by IS NOT NULL')
                                                    ->sum('total_before_vat');
                                                @endphp
                                                {{-- <label id="total_inv_lbl_{{ $i }}" class="col-form-label"
                                                    style="padding-bottom:0;">{{ number_format((($qRO?$qRO->total_before_vat:0)*$exchange_rate),0,".",",") }}</label><br/> --}}
                                                <label id="total_inv_lbl_{{ $i }}" class="col-form-label"
                                                    style="padding-bottom:0;">{{ number_format($qI->total_payment_before_retur,0,".",",") }}</label><br/>
                                                {{-- $qI --}}
                                                <label id="retur_val_{{ $i }}" class="col-form-label" style="color: red;padding-top:0;">{!! $totEveryReturBeforeVat !!}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @if ($qPaymentInv->supplier->supplier_type_id==10)
                                                    @php
                                                        $ro_vat_total += (($qI->total_payment/$total_before_vat_ro)*$total_vat_ro);
                                                    @endphp
                                                @endif
                                                <label for="" class="col-form-label">{{ number_format($qI->total_payment,0,".",",") }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $sumDibayar = \App\Models\Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pv.id', '=', 
                                                        'tx_payment_voucher_invoices.payment_voucher_id')
                                                    ->whereNotIn('tx_payment_voucher_invoices.payment_voucher_id', function ($q){
                                                        $q->select('id')
                                                        ->from('tx_payment_vouchers AS tx_pv')
                                                        ->whereRaw('payment_voucher_plan_no LIKE \'%Draft%\'')
                                                        ->where([
                                                            'active' => 'Y',
                                                        ]);
                                                    })
                                                    ->where('tx_payment_voucher_invoices.id', '<>', $qI->id)
                                                    ->where([
                                                        'tx_payment_voucher_invoices.receipt_order_id' => $receipt_id,
                                                        'tx_payment_voucher_invoices.active' => 'Y',
                                                        'tx_pv.tagihan_supplier_id' => $qPaymentInv->tagihan_supplier_id,
                                                        'tx_pv.active' => 'Y',
                                                    ])
                                                    ->sum('tx_payment_voucher_invoices.total_payment');
                                                @endphp
                                                <label for="" class="col-form-label">
                                                    {{ number_format($total_before_vat-$sumDibayar-$sumTotBeforeVat,0,".",",") }}
                                                    {{-- {{ number_format(floor($sumTotBeforeVat),0,".",",") }} --}}
                                                    {{-- {{ number_format(floor($qI->total_payment_before_retur-$sumTotBeforeVat-$qI->total_payment),0,".",",") }} --}}
                                                </label>
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                            $totTerbayar += $qI->total_payment;;
                                        @endphp
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td style="text-align: right;" colspan="5">Total</td>
                                    <td style="text-align: right">
                                        <label for="" id="tot-terbayar">{{ number_format($totTerbayar,0,".",",") }}</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;" colspan="5">VAT</td>
                                    <td style="text-align: right">
                                        <label for="" id="vat-terbayar">
                                            @if ($qPaymentInv->supplier->supplier_type_id==10)
                                                {{ number_format($ro_vat_total,0,".",",") }}
                                            @else
                                                {{ $ro_vat==0?0:number_format(($totTerbayar*$ro_vat)/100,0,".",",") }}
                                            @endif
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;" colspan="5">Total Biaya Lain-lain</td>
                                    <td style="text-align: right">
                                        @php
                                            $totalBiayaLain2 = $qPaymentInv->admin_bank+
                                                $qPaymentInv->biaya_kirim+
                                                $qPaymentInv->biaya_lainnya+
                                                $qPaymentInv->biaya_asuransi-
                                                $qPaymentInv->diskon_pembelian;
                                        @endphp
                                        <label for="" id="biaya-lain-lain">{{ number_format($totalBiayaLain2,0,".",",") }}</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;" colspan="5">Grand Total</td>
                                    <td style="text-align: right">
                                        <label for="" id="grand-tot-terbayar">{{ number_format($grandTotalTerbayar,0,".",",") }}</label>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="other-fee" class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="admin_bank" class="col-sm-3 col-form-label">Admin Bank  ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->admin_bank,0,".",",") }}</label>
                                <label for="admin_bank" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_asuransi" class="col-sm-3 col-form-label">Biaya Asuransi  ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->biaya_asuransi,0,".",",") }}</label>
                                <label for="biaya_asuransi" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Biaya Kirim  ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->biaya_kirim,0,".",",") }}</label>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Biaya Lainnya  ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->biaya_lainnya,0,".",",") }}</label>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Diskon Pembelian  ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->diskon_pembelian,0,".",",") }}</label>
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Credit</label>
                            </div>
                        </div>
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
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
