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
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">PC No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qPaymentInv->payment_receipt_plan_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">PA No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qPaymentInv->payment_receipt_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->customer->customer_unique_code.' - '.$qPaymentInv->customer->name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">NPWP</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->payment_type_id }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Metode Pembayaran</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @for ($i=0;$i<count($payment_mode_string);$i++)
                                        @if ($qPaymentInv->payment_mode==($i+1))
                                            {{ $payment_mode_string[$i] }}
                                        @endif
                                    @endfor
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Pembayaran Via</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->payment_reference?$qPaymentInv->payment_reference->title_ind:'' }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">No Rekening</label>
                                <label for="" class="col-sm-9 col-form-label">{{ ($qPaymentInv->coas?$qPaymentInv->coas->coa_name:'') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Transaction/Giro No</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qPaymentInv->reference_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Transaction/Giro Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($qPaymentInv->reference_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Total Pembayaran ({{ $qCurrency->string_val }})</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($qPaymentInv->payment_total,0,".",",") }}</label>
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
                                    <th scope="col" style="width: 12%;">Invoice No</th>
                                    <th scope="col" style="width: 15%;">FK No / RE No</th>
                                    <th scope="col" style="width: 10%;">FK No / RE Date</th>
                                    <th scope="col" style="width: 24%;">Description</th>
                                    <th scope="col" style="width: 12%;">Total ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Terbayar ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Sisa ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $i = 0;
                                    $totTerbayar = 0;
                                @endphp
                                @foreach ($queryInv as $qInv)
                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;">
                                            <label for="" class="col-form-label">{{ $i + 1 }}.</label>
                                            <input type="hidden" name="payment_receipt_id{{ $i }}" id="payment_receipt_id{{ $i }}" value="{{ $qInv->id }}">
                                        </th>
                                        <td>
                                            @php
                                                $inv_no = $qInv->invoice_no;
                                            @endphp
                                            <label for="" class="col-form-label">{{ $inv_no }}</label>
                                        </td>
                                        <td>
                                            @php
                                                $fk_nr = '';
                                                $fk_nr_date = '';
                                                $nr_total_before_vat = '';
                                                $total_must_be_paid = 0;
                                                $qInvS = [];
                                                if (strpos("invoice-".$inv_no,env('P_INVOICE'))>0){
                                                    $qInvS = \App\Models\Tx_invoice_detail::leftjoin('tx_delivery_orders as fk','tx_invoice_details.fk_id','=','fk.id')
                                                    ->leftJoin('tx_nota_returs as nr','fk.id','=','nr.delivery_order_id')
                                                    ->leftJoin('tx_invoices AS inv','tx_invoice_details.invoice_id','=','inv.id')
                                                    ->select(
                                                        'fk.delivery_order_no',
                                                        'fk.delivery_order_date',
                                                        'nr.nota_retur_no',
                                                        'nr.nota_retur_date',
                                                        'nr.total_before_vat',
                                                        // 'tx_invoice_details.total as total_before_vat',
                                                    )
                                                    ->where([
                                                        'inv.invoice_no'=>$inv_no,
                                                        'fk.active'=>'Y',
                                                        'tx_invoice_details.active'=>'Y',
                                                    ])
                                                    ->get();
                                                }

                                                if (strpos("invoice-".$inv_no,env('P_KWITANSI'))>0){
                                                    $qInvS = \App\Models\Tx_kwitansi_detail::leftjoin('tx_delivery_order_non_taxes as np','tx_kwitansi_details.np_id','=','np.id')
                                                    ->leftJoin('tx_nota_retur_non_taxes as nr','np.id','=','nr.delivery_order_id')
                                                    ->leftJoin('tx_kwitansis AS inv','tx_kwitansi_details.kwitansi_id','=','inv.id')
                                                    ->select(
                                                        'np.delivery_order_no',
                                                        'np.delivery_order_date',
                                                        'nr.nota_retur_no',
                                                        'nr.nota_retur_date',
                                                        'nr.total_price as total_before_vat',
                                                        // 'tx_kwitansi_details.total as total_before_vat',
                                                    )
                                                    ->where([
                                                        'inv.kwitansi_no'=>$inv_no,
                                                        'np.active'=>'Y',
                                                        'tx_kwitansi_details.active'=>'Y',
                                                    ])
                                                    ->get();
                                                }
                                            @endphp
                                            @foreach ($qInvS as $inv)
                                                @php
                                                    $fk_nr .= $inv->delivery_order_no.($inv->nota_retur_no!=null?'<br/>'.$inv->nota_retur_no.'<br/>':'<br/>');
                                                    $nr_total_before_vat .= ($inv->total_before_vat!=null?'('.number_format($inv->total_before_vat,0,".",",").')<br/>':'');
                                                    // $nr_total_before_vat .= '('.number_format(($inv->total_before_vat!=null?$inv->total_before_vat:0),0,".",",").')<br/>';
                                                    $total_must_be_paid += ($inv->total_before_vat!=null?$inv->total_before_vat:0);
                                                @endphp
                                            @endforeach
                                            <label id="fk_id_nr_id_{{ $i }}" class="col-form-label">{!! $fk_nr !!}</label>
                                        </td>
                                        <td>
                                            @foreach ($qInvS as $inv)
                                                @php
                                                    $fk_nr_date .= date_format(date_create($inv->delivery_order_date), "d/m/Y").
                                                        ($inv->nota_retur_date!=null?'<br/>'.date_format(date_create($inv->nota_retur_date), "d/m/Y").'<br/>':'<br/>');
                                                @endphp
                                            @endforeach
                                            <label id="fk_date_nr_date_{{ $i }}" class="col-form-label">{!! $fk_nr_date !!}</label>
                                        </td>
                                        <td>
                                            <label for="" class="col-form-label">{{ $qInv->description }}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label id="total_must_be_paid_{{ $i }}" class="col-form-label"
                                                style="padding-bottom:0;">{!! number_format($total_must_be_paid+$qInv->total_payment_full,0,".",",") !!}</label><br/>
                                            <label id="nr_total_before_vat_lbl_{{ $i }}" class="col-form-label"
                                                style="padding-bottom:0;color:red;">{!! $nr_total_before_vat !!}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label id="total_inv_{{ $i }}" class="col-form-label"
                                                style="padding-bottom:0;">{!! number_format($qInv->total_payment,0,".",",") !!}</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label id="" class="col-form-label" style="padding-bottom:0;">{{ number_format($qInv->total_payment_full-$qInv->total_payment,0,".",",") }}</label>
                                        </td>
                                    </tr>
                                    @php
                                        $totTerbayar += $qInv->total_payment;
                                        $i += 1;
                                    @endphp
                                @endforeach
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
                                        <label for="" id="vat-terbayar">{{ ($qPaymentInv->payment_type_id=='P'?number_format(($totTerbayar*$qVat->numeric_val)/100,0,".",","):0) }}</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;" colspan="5">Total Biaya Lainnya</td>
                                    <td style="text-align: right">
                                        <label for="" id="tot-biaya-lainnya">
                                            {{ number_format(($qPaymentInv->biaya_kirim+$qPaymentInv->penerimaan_lainnya-$qPaymentInv->diskon_pembelian-$qPaymentInv->admin_bank),0,".",",") }}
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;" colspan="5">Grand Total</td>
                                    <td style="text-align: right">
                                        <label for="" id="grand-tot-terbayar">
                                            {{ ($qPaymentInv->payment_type_id=='P'?
                                                number_format(($totTerbayar+(($totTerbayar*$qVat->numeric_val)/100)+($qPaymentInv->biaya_kirim+$qPaymentInv->penerimaan_lainnya-$qPaymentInv->diskon_pembelian-$qPaymentInv->admin_bank)),0,".",","):
                                                number_format(($totTerbayar+($qPaymentInv->biaya_kirim+$qPaymentInv->penerimaan_lainnya-$qPaymentInv->diskon_pembelian-$qPaymentInv->admin_bank)),0,".",",")) }}
                                        </label>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div id="other-fee" class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="diskon_pembelian" class="col-sm-3 col-form-label">Diskon ({{ $qCurrency->string_val }})</label>
                            <label for="" id="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->diskon_pembelian,0,".",",") }}</label>
                            <label for="diskon_pembelian" class="col-sm-3 col-form-label">Debet</label>
                        </div>
                        <div class="row mb-3">
                            <label for="admin_bank" class="col-sm-3 col-form-label">Admin Bank ({{ $qCurrency->string_val }})</label>
                            <label for="" id="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->admin_bank,0,".",",") }}</label>
                            <label for="admin_bank" class="col-sm-3 col-form-label">Debet</label>
                        </div>
                        <div class="row mb-3">
                            <label for="penerimaan_lainnya" class="col-sm-3 col-form-label">Penerimaan Lainnya  ({{ $qCurrency->string_val }})</label>
                            <label for="" id="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->penerimaan_lainnya,0,".",",") }}</label>
                            <label for="penerimaan_lainnya" class="col-sm-3 col-form-label">Credit</label>
                        </div>
                        <div class="row mb-3">
                            <label for="biaya_kirim" class="col-sm-3 col-form-label">Biaya Kirim  ({{ $qCurrency->string_val }})</label>
                            <label for="" id="" class="col-sm-3 col-form-label">{{ number_format($qPaymentInv->biaya_kirim,0,".",",") }}</label>
                            <label for="biaya_kirim" class="col-sm-3 col-form-label">Credit</label>
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
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
