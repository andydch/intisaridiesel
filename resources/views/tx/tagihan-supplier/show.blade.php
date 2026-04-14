@extends('layouts.app')

@section('style')
<style>
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
                        @if (session('status-error'))
                            <div class="alert alert-danger">
                                {{ session('status-error') }}
                            </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">No Tagihan Supplier</label>
                                            <label for="" class="col-sm-9 col-form-label part-id">{{ $qTS->tagihan_supplier_no }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="supplier_id" class="col-sm-3 col-form-label">Supplier</label>
                                            <label for="supplier_id" class="col-sm-9 col-form-label">
                                                {{ $qTS->supplier->supplier_code.' - '.($qTS->supplier->entity_type?$qTS->supplier->entity_type->title_ind:'').' '.$qTS->supplier->name }}
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">INV No / RO No</label>
                                            <div class="col-sm-9">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <table class="table table-bordered mb-0">
                                                            <thead>
                                                                <tr style="width: 100%;">
                                                                    <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                    <th scope="col" style="width: 97%;text-align:center;">INV No / RO No</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="new-row-ro">
                                                                @php
                                                                    $vat_val = '';
                                                                    $iRow = 0;
                                                                    $receipt_order_no_all = '';
                                                                @endphp
                                                                @foreach ($qRO_selected->get() as $qRO_s)
                                                                    <tr id="rowRO{{ $iRow }}">
                                                                        <td scope="row" style="text-align:right;">
                                                                            <label for="" class="col-form-label" id="ro_row_number{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                                            <input type="hidden" name="ts_dtl{{ $iRow }}" id="ts_dtl{{ $iRow }}" value="{{ $qRO_s->qTS_dtl_id }}">
                                                                            <input type="hidden" name="receipt_order_idRO{{ $iRow }}" id="receipt_order_idRO{{ $iRow }}" 
                                                                                value="{{ $qRO_s->ro_id }}">
                                                                        </td>
                                                                        <td>
                                                                            @php
                                                                                // Formatting a specific past/future date using strtotime()
                                                                                $specificDate = date('d-m-Y', strtotime($qRO_s->receipt_date));

                                                                                $ro_lbl = $qRO_s->invoice_no.' ('.$specificDate.') / '.$qRO_s->receipt_no.' ('.($qRO_s->journal_type_id=='N'?'Non VAT':'VAT').')';
                                                                            @endphp
                                                                            <label for="" name="receipt_order_no_select{{ $iRow }}" id="receipt_order_no_select{{ $iRow }}"
                                                                                class="col-form-label">{{ $ro_lbl }}</label>
                                                                        </td>
                                                                    </tr>
                                                                    @php
                                                                        $receipt_order_no_all .= $ro_lbl.',';
                                                                        $vat_val = ($qRO_s->vat_val>0?'VAT':'Non VAT');
                                                                        $iRow+= 1;
                                                                    @endphp
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        <input type="hidden" id="totalRowRO" name="totalRowRO" value="{{ $qRO_selected->count() }}">
                                                        <input type="hidden" name="receipt_order_no_all" id="receipt_order_no_all" value="{{ $receipt_order_no_all }}">
                                                        <input type="hidden" name="vat_val" id="vat_val" value="{{ '('.$vat_val.')' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="payment_plan_date" class="col-sm-3 col-form-label">Payment Plan Date</label>
                                            @php
                                                $date=date_create($qTS->tagihan_supplier_date);
                                            @endphp 
                                            <label for="payment_plan_date" class="col-sm-9 col-form-label">{{ date_format($date,"d/m/Y") }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Bank</span>
                                            <span class="col-sm-9 col-form-label">{{ $qTS->bank->coa_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-0 text-uppercase">Receipt Order & Invoice</h6>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        @php
                            $lastTotalAmount = 0;
                        @endphp
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr style="width: 100%;">
                                    <th scope="col" style="width: 3%;text-align:center;">#</th>
                                    <th scope="col" style="width: 24%;">RO No</th>
                                    <th scope="col" style="width: 24%;">INV No</th>
                                    <th scope="col" style="width: 24%;">PR No</th>
                                    <th scope="col" style="width: 25%;">Total Price ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $iRow = 0;
                                    $lastVatTmp = 0;
                                    $lastVatVal = 0;
                                @endphp
                                @foreach ($qRO_selected->get() as $qRO_s)
                                    @php
                                        $qS = \App\Models\Tx_receipt_order::select(
                                            'tx_receipt_orders.id as ro_id',
                                            'tx_receipt_orders.receipt_no',
                                            'tx_receipt_orders.invoice_no',
                                            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
                                            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
                                            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                                                tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
                                            'tx_receipt_orders.vat_val',
                                            'tx_receipt_orders.active as ro_active',
                                        )
                                        ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                                        ->where([
                                            'tx_receipt_orders.id' => $qRO_s->ro_id,
                                            'tx_receipt_orders.active' => 'Y',
                                        ])
                                        ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                                        ->first();
                                    @endphp
                                    @if ($qS)
                                        @php
                                            $pr_total_before_vat = 0;
                                            $pr_total_vat = 0;
                                            $purchase_retur_no = '';
                                            $qPR = \App\Models\Tx_purchase_retur::where('receipt_order_id', $qRO_s->ro_id)
                                            ->whereRaw('approved_by IS NOT NULL')
                                            ->where('is_draft', 'N')
                                            ->where('active', 'Y')
                                            ->get();
                                            foreach ($qPR as $pr) {
                                                $purchase_retur_no .= $pr->purchase_retur_no.'<br/>';
                                                $pr_total_before_vat += $pr->total_before_vat;
                                                $pr_total_vat += ($pr->total_after_vat-$pr->total_before_vat);
                                            }
                                        @endphp
                                        <tr id="rowROdtl{{ $iRow }}">
                                            <td scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label" id="ro_row_number_dtl{{ $iRow }}">{{ $iRow+1 }}.&nbsp;</label>
                                                <input type="hidden" name="receipt_order_id_dtl_{{ $iRow }}" id="receipt_order_id_dtl_{{ $iRow }}" value="{{ $qS->ro_id }}">
                                                <input type="hidden" name="vat_dtl_{{ $iRow }}" id="vat_dtl_{{ $iRow }}" value="{{ $qS->vat_val }}">
                                            </td>
                                            <td><label for="" name="ro_no_{{ $iRow }}" id="ro_no_{{ $iRow }}" class="col-form-label">{{ $qS->receipt_no }}</label></td>
                                            <td><label for="" name="inv_no_{{ $iRow }}" id="inv_no_{{ $iRow }}" class="col-form-label">{{ $qS->invoice_no }}</label></td>
                                            <td>
                                                <label for="" name="prc_retur_no_{{ $iRow }}" id="prc_retur_no_{{ $iRow }}" class="col-form-label">
                                                    {!! $purchase_retur_no !!}
                                                </label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total_price_{{ $iRow }}" id="total_price_{{ $iRow }}" 
                                                    class="col-form-label">{{ number_format($qS->ro_total_before_vat-$pr_total_before_vat,0,'.',',') }}
                                                </label>
                                            </td>
                                        </tr>
                                        @php
                                            $lastVatTmp = $qS->vat_val;
                                            $lastVatVal += ($qS->ro_total_vat-$pr_total_vat);
                                            $lastTotalAmount += ($qS->ro_total_before_vat-$pr_total_before_vat);
                                        @endphp
                                    @endif
                                    @php
                                        $iRow++;
                                    @endphp
                                @endforeach
                                <tr id="rowTotal">
                                    <td colspan="4" style="text-align: right;">
                                        <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblTotalAmount" id="lblTotalAmount" 
                                            class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                    </td>
                                </tr>
                                <tr id="rowVAT">
                                    <td colspan="4" style="text-align: right;">
                                        <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblVATAmount" id="lblVATAmount" 
                                            class="col-form-label">{{ $qCurrency->string_val.number_format($lastVatVal,0,'.',',') }}
                                        </label>
                                        {{-- <label for="" name="lblVATAmount" id="lblVATAmount" 
                                            class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount*$lastVatTmp/100,0,'.',',') }}
                                        </label> --}}
                                    </td>
                                </tr>
                                <tr id="rowGrandTotal">
                                    <td colspan="4" style="text-align: right;">
                                        <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                    </td>
                                    <td style="text-align: right;">
                                        <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" 
                                            class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+$lastVatVal,0,'.',',') }}
                                        </label>
                                        {{-- <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" 
                                            class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastTotalAmount*$lastVatTmp/100),0,'.',',') }}
                                        </label> --}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- </form> --}}
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri) }}";            
        });
    });
</script>
@endsection
