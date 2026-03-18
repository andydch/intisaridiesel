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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
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
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="customer_id" class="col-sm-3 col-form-label">Customer*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $customerId = (old('customer_id')?old('customer_id'):0);
                                            @endphp
                                            @foreach ($customers as $p)
                                                <option @if ($customerId==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->customer_unique_code.' - '.$p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="payment_type_id" class="col-sm-3 col-form-label">NPWP*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('payment_type_id') is-invalid @enderror" id="payment_type_id" name="payment_type_id">
                                            <option value="">Choose...</option>
                                            @php
                                                $payment_type_id = (old('payment_type_id')?old('payment_type_id'):0);
                                            @endphp
                                            @foreach ($payment_type as $t)
                                                <option @if($payment_type_id==$t){{ 'selected' }}@endif value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="payment_mode_id" class="col-sm-3 col-form-label">Metode Pembayaran*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('payment_mode_id') is-invalid @enderror" id="payment_mode_id" name="payment_mode_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $paymentModeId = (old('payment_mode_id')?old('payment_mode_id'):0);
                                            @endphp
                                            @for ($i=0;$i<count($payment_mode_string);$i++)
                                                <option @if($paymentModeId==($i+1)){{ 'selected' }}@endif
                                                    value="{{ $i+1 }}">{{ $payment_mode_string[$i] }}</option>
                                            @endfor
                                        </select>
                                        @error('payment_mode_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="ref_id" class="col-sm-3 col-form-label">Pembayaran Via*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('ref_id') is-invalid @enderror" id="ref_id" name="ref_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $prefId = (old('ref_id')?old('ref_id'):0);
                                                $paymentRef = \App\Models\Mst_global::select(
                                                    'id',
                                                    'title_ind',
                                                    'title_eng',
                                                    'slug',
                                                )
                                                ->when($paymentModeId==1, function($q){
                                                    // 51: Cash

                                                    $q->whereIn('id',[51]);
                                                })
                                                ->when($paymentModeId==2, function($q){
                                                    // 49: EDC
                                                    // 50: Giro
                                                    // 63: Bank Transfer
                                                    
                                                    $q->whereIn('id',[49,50,63]);
                                                })
                                                ->when($paymentModeId==3, function($q){
                                                    // 9999: <empty>

                                                    // $q->whereIn('id',[9999]);
                                                    $q->where([
                                                        'data_cat' => 'payment-ref',
                                                        'slug' => 'customer-deposit',
                                                    ]);
                                                })
                                                ->where([
                                                    'data_cat'=>'payment-ref',
                                                    'active'=>'Y',
                                                ])
                                                ->orderBy('title_ind','ASC')
                                                ->get();
                                            @endphp
                                            @foreach ($paymentRef as $pr)
                                                <option @if($prefId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $pr->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('ref_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="coa_id" class="col-sm-3 col-form-label">No Rekening*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('coa_id') is-invalid @enderror" id="coa_id" name="coa_id">
                                            <option value="">Choose...</option>
                                            @php
                                                $coaId = (old('coa_id')?old('coa_id'):0);
                                                $payment_mode_id = old('payment_mode_id')?old('payment_mode_id'):0;
                                                $payment_group = ($payment_type_id=='P'?7:14);
                                                $customer_id = $customerId;
                                                $branch_id = 0;

                                                $qCustomers = \App\Models\Mst_customer::where([
                                                    'id'=>$customer_id,
                                                    'active'=>'Y',
                                                ])
                                                ->first();
                                                if($qCustomers){
                                                    $branch_id = $qCustomers->branch_id;
                                                }

                                                $queryCoa = \App\Models\Mst_coa::select(
                                                    'id',
                                                    'coa_name'
                                                )
                                                ->where(function($q) use($payment_mode_id, $payment_group, $branch_id){
                                                    $q->whereIn('id', function($q1) use($payment_mode_id, $payment_group, $branch_id){
                                                        $q1->select('coa_code_id')
                                                        ->from('mst_automatic_journal_details')
                                                        ->where([
                                                            'auto_journal_id'=>$payment_group,
                                                            'method_id'=>$payment_mode_id,  // 1:cash/2:bank/3:customer deposit
                                                            'branch_id'=>$branch_id, // cabang
                                                            'active'=>'Y',
                                                        ])
                                                        ->whereRaw('LOWER(`desc`) IN (\'bank\',\'cash\',\'customer deposit\')');
                                                    })
                                                    ->orWhereIn('id', function($q2) use($payment_mode_id, $payment_group, $branch_id){
                                                        $q2->select('coa_code_id')
                                                        ->from('mst_automatic_journal_detail_exts')
                                                        ->where([
                                                            'auto_journal_id'=>$payment_group,
                                                            'method_id'=>$payment_mode_id,  // 1:cash/2:bank/3:customer deposit
                                                            'branch_id'=>$branch_id, // cabang
                                                            'active'=>'Y',
                                                        ])
                                                        ->whereRaw('LOWER(`desc`) IN (\'bank\',\'cash\',\'customer deposit\')');
                                                    });
                                                })
                                                ->where([
                                                    'active' => 'Y'
                                                ])
                                                ->orderBy('coa_name','ASC')
                                                ->get();
                                            @endphp
                                            @foreach ($queryCoa as $coa)
                                                <option @if($coaId==$coa->id){{ 'selected' }}@endif value="{{ $coa->id }}">{{ $coa->coa_name }}</option>
                                            @endforeach
                                            {{-- @if ($paymentModeId==1 || $paymentModeId==2)
                                            @endif --}}
                                        </select>
                                        @error('coa_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="reference_no" class="col-sm-3 col-form-label">Transaction/Giro No</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('reference_no') is-invalid @enderror"
                                            maxlength="255" id="reference_no" name="reference_no" placeholder="Reference No"
                                            value="@if (old('reference_no')){{ old('reference_no') }}@endif">
                                        @error('reference_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="reference_date" class="col-sm-3 col-form-label">Transaction/Giro Date</label>
                                    <div class="col-sm-9">
                                        <input readonly type="text" class="form-control @error('reference_date') is-invalid @enderror"
                                            maxlength="10" id="reference_date" name="reference_date" placeholder="Reference Date"
                                            value="@if (old('reference_date')){{ old('reference_date') }}@endif">
                                        @error('reference_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="total_payment" class="col-sm-3 col-form-label">Total Pembayaran ({{ $qCurrency->string_val }})*</label>
                                    <div class="col-sm-9">
                                        <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('total_payment') is-invalid @enderror"
                                            maxlength="50" id="total_payment" name="total_payment" placeholder="Total"
                                            value="@if (old('total_payment')){{ old('total_payment') }}@endif">
                                        @error('total_payment')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="payment_date" class="col-sm-3 col-form-label">Journal Date*</label>
                                    <div class="col-sm-9">
                                        <input readonly type="text" class="form-control @error('payment_date') is-invalid @enderror"
                                            maxlength="10" id="payment_date" name="payment_date" placeholder="Date"
                                            value="@if (old('payment_date')){{ old('payment_date') }}@endif">
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                    <div class="col-sm-9">
                                        <textarea name="remark" id="remark" rows="3" maxlength="2000" style="width: 100%;"
                                            class="form-control @error('remark') is-invalid @enderror">@if (old('remark')){{ old('remark') }}@endif</textarea>
                                        @error('remark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <th scope="col" style="width: 12%;">Invoice No</th>
                                        <th scope="col" style="width: 15%;">FK No / RE No</th>
                                        <th scope="col" style="width: 10%;">FK No / RE Date</th>
                                        <th scope="col" style="width: 21%;">Description</th>
                                        <th scope="col" style="width: 12%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 12%;">Terbayar ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 12%;">Sisa ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $totTerbayar = 0;
                                    @endphp
                                    @for ($i = 0; $i < $totRow; $i++)
                                        @if (old('invoice_no_'.$i))
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label for="" id="payment_receipt_row_number{{ $i }}" class="col-form-label">{{ $i+1 }}.</label>
                                                </th>
                                                <td>
                                                    @php
                                                        $inv_no = '';
                                                    @endphp
                                                    <select onchange="dispTotPrice(this.value, {{ $i }});" class="form-select single-select @error('invoice_no_'.$i) is-invalid @enderror"
                                                        id="invoice_no_{{ $i }}" name="invoice_no_{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $roId = old('invoice_no_'.$i)?old('invoice_no_'.$i):0;
                                                        @endphp
                                                        @foreach ($invoices as $ro)
                                                            @if ($roId==$ro->invoice_no)
                                                                @php
                                                                    $inv_no = $ro->invoice_no;
                                                                @endphp
                                                            @endif
                                                            <option @if ($roId==$ro->invoice_no){{ 'selected' }}@endif value="{{ $ro->invoice_no }}">{{ $ro->invoice_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('invoice_no_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    @php
                                                        $fk_nr = '';
                                                        $fk_nr_date = '';
                                                        $nr_total_before_vat = '';
                                                        $total_must_be_paid = 0;
                                                        $qInv = [];
                                                        if (strpos("invoice-".$inv_no,env('P_INVOICE'))>0){
                                                            $qInv = \App\Models\Tx_invoice_detail::leftjoin('tx_delivery_orders as fk','tx_invoice_details.fk_id','=','fk.id')
                                                            ->leftJoin('tx_nota_returs as nr','fk.id','=','nr.delivery_order_id')
                                                            ->leftJoin('tx_invoices AS inv','tx_invoice_details.invoice_id','=','inv.id')
                                                            ->select(
                                                                'fk.delivery_order_no',
                                                                'fk.delivery_order_date',
                                                                'nr.nota_retur_no',
                                                                'nr.nota_retur_date',
                                                                'nr.total_before_vat',
                                                            )
                                                            ->where([
                                                                'inv.invoice_no'=>$inv_no,
                                                                'fk.active'=>'Y',
                                                                'tx_invoice_details.active'=>'Y',
                                                            ])
                                                            ->get();
                                                        }

                                                        if (strpos("invoice-".$inv_no,env('P_KWITANSI'))>0){
                                                            $qInv = \App\Models\Tx_kwitansi_detail::leftjoin('tx_delivery_order_non_taxes as np','tx_kwitansi_details.np_id','=','np.id')
                                                            ->leftJoin('tx_nota_retur_non_taxes as nr','np.id','=','nr.delivery_order_id')
                                                            ->leftJoin('tx_kwitansis AS inv','tx_kwitansi_details.kwitansi_id','=','inv.id')
                                                            ->select(
                                                                'np.delivery_order_no',
                                                                'np.delivery_order_date',
                                                                'nr.nota_retur_no',
                                                                'nr.nota_retur_date',
                                                                'nr.total_price as total_before_vat',
                                                            )
                                                            ->where([
                                                                'inv.kwitansi_no'=>$inv_no,
                                                                'np.active'=>'Y',
                                                                'tx_kwitansi_details.active'=>'Y',
                                                            ])
                                                            ->get();
                                                        }
                                                    @endphp
                                                    @foreach ($qInv as $inv)
                                                        @php
                                                            $fk_nr .= $inv->delivery_order_no.($inv->nota_retur_no!=null?'<br/>'.$inv->nota_retur_no.'<br/>':'<br/>');
                                                            $nr_total_before_vat .= '('.number_format(($inv->total_before_vat!=null?$inv->total_before_vat:0),0,".",",").')<br/>';
                                                            $total_must_be_paid += ($inv->total_before_vat!=null?$inv->total_before_vat:0);
                                                        @endphp
                                                    @endforeach
                                                    <label id="fk_id_nr_id_{{ $i }}" class="col-form-label">{!! $fk_nr !!}</label>
                                                </td>
                                                <td>
                                                    @foreach ($qInv as $inv)
                                                        @php
                                                            $fk_nr_date .= date_format(date_create($inv->delivery_order_date), "d/m/Y").
                                                                ($inv->nota_retur_date!=null?'<br/>'.date_format(date_create($inv->nota_retur_date), "d/m/Y").'<br/>':'<br/>');
                                                        @endphp
                                                    @endforeach
                                                    <label id="fk_date_nr_date_{{ $i }}" class="col-form-label">{!! $fk_nr_date !!}</label>
                                                </td>
                                                <td>
                                                    <textarea class="form-control @error('desc_'.$i) is-invalid @enderror"
                                                        name="desc_{{ $i }}" id="desc_{{ $i }}" rows="3"
                                                        style="width: 100%;">@if (old('desc_'.$i)){{ old('desc_'.$i) }}@endif</textarea>
                                                    @error('desc_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="total_must_be_paid_{{ $i }}" class="col-form-label"
                                                        style="padding-bottom:0;">{!! number_format($total_must_be_paid+old('total_inv_o_'.$i),0,".",",") !!}</label><br/>
                                                    <label id="nr_total_before_vat_lbl_{{ $i }}" class="col-form-label" style="padding-bottom:0;color:red;">{!! $nr_total_before_vat !!}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('total_inv_'.$i) is-invalid @enderror"
                                                        id="total_inv_{{ $i }}" name="total_inv_{{ $i }}" maxlength="25"
                                                        value="@if (old('total_inv_'.$i)){{ old('total_inv_'.$i) }}@endif" style="text-align: right;" />
                                                    <input type="hidden" id="total_inv_o_{{ $i }}" name="total_inv_o_{{ $i }}"
                                                        maxlength="25" value="@if (old('total_inv_o_'.$i)){{ old('total_inv_o_'.$i) }}@endif" />
                                                    @error('total_inv_'.$i)
                                                        <div class="invalid-feedback" style="text-align: left;">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">0</td>
                                                <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                            </tr>
                                            @php
                                                $totTerbayar += str_replace(",","",old('total_inv_'.$i));
                                            @endphp
                                        @endif
                                    @endfor
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
                                            <label for="" id="vat-terbayar">{{ ($payment_type_id=='P'?number_format(($totTerbayar*$qVat->numeric_val)/100,0,".",","):0) }}</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;" colspan="5">Total Biaya Lainnya</td>
                                        <td style="text-align: right">
                                            <label for="" id="tot-biaya-lainnya">
                                                @php
                                                    $diskon_pembelian = (is_numeric(str_replace(",","",old('diskon_pembelian')))?str_replace(",","",old('diskon_pembelian')):0);
                                                    $admin_bank = (is_numeric(str_replace(",","",old('admin_bank')))?str_replace(",","",old('admin_bank')):0);
                                                    $biaya_kirim = (is_numeric(str_replace(",","",old('biaya_kirim')))?str_replace(",","",old('biaya_kirim')):0);
                                                    $penerimaan_lainnya = (is_numeric(str_replace(",","",old('penerimaan_lainnya')))?str_replace(",","",old('penerimaan_lainnya')):0);
                                                @endphp
                                                {{ number_format($biaya_kirim+$penerimaan_lainnya-$diskon_pembelian-$admin_bank,0,".",",") }}
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;" colspan="5">Grand Total</td>
                                        <td style="text-align: right">
                                            @php
                                                $grandTotal = ($payment_type_id=='P'?
                                                    ($totTerbayar+(($totTerbayar*$qVat->numeric_val)/100)+($biaya_kirim+$penerimaan_lainnya-$diskon_pembelian-$admin_bank)):
                                                    ($totTerbayar+($biaya_kirim+$penerimaan_lainnya-$diskon_pembelian-$admin_bank)));
                                            @endphp
                                            <label for="" id="grand-tot-terbayar">{{ number_format($grandTotal,0,".",",") }}</label>
                                            <input type="hidden" name="grand_tot_terbayar_val" id="grand-tot-terbayar-val" value="{{ $grandTotal }}">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                                <input type="hidden" name="other_fee_status" id="other_fee_status" value="@if(old('other_fee_status')){{ old('other_fee_status') }}@endif">
                            </div>
                        </div>
                    </div>
                    <div id="other-fee" class="card" style="margin-top: 15px;">
                        @php
                            $readonlyOtherFee = "";
                            $bgColor = "#fff";
                        @endphp
                        @if (old('payment_mode_id')==1)
                            @php
                                $readonlyOtherFee = "readonly";
                                $bgColor = "gray";
                            @endphp
                        @endif
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Diskon ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('diskon_pembelian') is-invalid @enderror"
                                        id="diskon_pembelian" name="diskon_pembelian" placeholder="0" value="@if(old('diskon_pembelian')){{ old('diskon_pembelian') }}@endif">
                                    @error('diskon_pembelian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="diskon_pembelian" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="admin_bank" class="col-sm-3 col-form-label">Admin Bank ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input {{ $readonlyOtherFee }} onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('admin_bank') is-invalid @enderror"
                                        id="admin_bank" name="admin_bank" placeholder="0" value="@if(old('admin_bank')){{ old('admin_bank') }}@endif" 
                                        style="background-color: {{ $bgColor }};">
                                    @error('admin_bank')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="admin_bank" class="col-sm-3 col-form-label">Debet</label>
                            </div>
                            <div class="row mb-3">
                                <label for="penerimaan_lainnya" class="col-sm-3 col-form-label">Penerimaan Lainnya  ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('penerimaan_lainnya') is-invalid @enderror"
                                        id="penerimaan_lainnya" name="penerimaan_lainnya" placeholder="0" value="@if(old('penerimaan_lainnya')){{ old('penerimaan_lainnya') }}@endif">
                                    @error('penerimaan_lainnya')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Credit</label>
                            </div>
                            <div class="row mb-3">
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Biaya Kirim  ({{ $qCurrency->string_val }})</label>
                                <div class="col-sm-6">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('biaya_kirim') is-invalid @enderror"
                                        id="biaya_kirim" name="biaya_kirim" placeholder="0" value="@if(old('biaya_kirim')){{ old('biaya_kirim') }}@endif">
                                    @error('biaya_kirim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="biaya_kirim" class="col-sm-3 col-form-label">Credit</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    <input type="button" id="save-plan" class="btn btn-primary px-5" value="Save Plan">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
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
    function formatPartPrice(elm){
        let priceList = $(elm).val().replaceAll(',','');
        if(priceList===''){priceList = 0;$(elm).val(0);}
        if(isNaN(priceList)){priceList = 0;$(elm).val(0);}
        priceList = parseFloat(priceList).numberFormat(0,'.',',');
        $(elm).val(priceList);

        // set cursor position
        // if($(elm).val().length>=3){
        //     $(elm).selectRange($(elm).val().length-3); // set cursor position
        // }

        sumTerbayar();
    }

    function sumTerbayar(){
        let totRows = parseInt($("#totalRow").val());
        let totNum = 0;
        for(let iRow=0;iRow<totRows;iRow++){
            if ($('#total_inv_'+iRow).val()!=undefined && $('#total_inv_'+iRow).val()!=''){
                totNum += parseFloat($('#total_inv_'+iRow).val().replaceAll(',',''));
            }
        }
        $('#tot-terbayar').text(parseFloat(totNum).numberFormat(0,'.',','));

        let diskon_pembelian = $('#diskon_pembelian').val().replaceAll(',','');
        diskon_pembelian = ($.isNumeric(diskon_pembelian)?diskon_pembelian:0);
        let admin_bank = $('#admin_bank').val().replaceAll(',','');
        admin_bank = ($.isNumeric(admin_bank)?admin_bank:0);
        let biaya_kirim = $('#biaya_kirim').val().replaceAll(',','');
        biaya_kirim = ($.isNumeric(biaya_kirim)?biaya_kirim:0);
        let penerimaan_lainnya = $('#penerimaan_lainnya').val().replaceAll(',','');
        penerimaan_lainnya = ($.isNumeric(penerimaan_lainnya)?penerimaan_lainnya:0);
        let tot_biaya_lainnya = parseFloat(biaya_kirim)+parseFloat(penerimaan_lainnya)-parseFloat(admin_bank)-parseFloat(diskon_pembelian);
        $('#tot-biaya-lainnya').text(parseFloat(tot_biaya_lainnya).numberFormat(0,'.',','));

        let vat = 0;
        let vat_num = 0;
        if ($('#payment_type_id option:selected').val()=='P'){
            vat = {{ $qVat->numeric_val }};
            vat_num = (totNum*vat)/100;
            $('#vat-terbayar').text(parseFloat(vat_num).numberFormat(0,'.',','));
            $('#grand-tot-terbayar').text(parseFloat(totNum+vat_num+tot_biaya_lainnya).numberFormat(0,'.',','));
            $('#grand-tot-terbayar-val').val(totNum+vat_num+tot_biaya_lainnya);
        }else{
            $('#grand-tot-terbayar').text(parseFloat(totNum+tot_biaya_lainnya).numberFormat(0,'.',','));
            $('#grand-tot-terbayar-val').val(totNum+tot_biaya_lainnya);
        }
    }

    function dispTotPrice(invId, idx){
        var fd = new FormData();
        fd.append('invId', invId);
        fd.append('payment_type_id', $('#payment_type_id option:selected').val());
        $.ajax({
            url: "{{ url('/disp_pa_inv_totalprice_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let total_must_be_paid = 0;
                let o = res[0].inv;
                $("#total_inv_"+idx).val(parseFloat(o.total_price).numberFormat(0,'.',','));
                $("#total_inv_o_"+idx).val(o.total_price);
                let p = res[0].fk_info;
                let nr_total_before_vat = '';
                if(p.length>0){
                    let fk_nr='';
                    let fk_nr_date='';
                    for(i=0;i<p.length;i++){
                        fk_nr+=p[i].delivery_order_no+(p[i].nota_retur_no!=null?'<br/>'+p[i].nota_retur_no+'<br/>':'<br/>');
                        fk_nr_date+=p[i].delivery_order_date+(p[i].nota_retur_no!=null?'<br/>'+p[i].nota_retur_date+'<br/>':'<br/>');
                        nr_total_before_vat+='('+parseFloat((p[i].total_before_vat!=null?p[i].total_before_vat:0)).numberFormat(0,'.',',')+')<br/>';
                        total_must_be_paid += parseFloat((p[i].total_before_vat!=null?p[i].total_before_vat:0));
                    }
                    $("#fk_id_nr_id_"+idx).html(fk_nr);
                    $("#fk_date_nr_date_"+idx).html(fk_nr_date);
                    $("#nr_total_before_vat_lbl_"+idx).html(nr_total_before_vat);
                    total_must_be_paid = parseFloat(total_must_be_paid)+parseFloat(o.total_price);
                    $("#total_must_be_paid_"+idx).text(parseFloat(total_must_be_paid).numberFormat(0,'.',','));

                    sumTerbayar();
                }
            },
        });
    }

    $(document).ready(function() {
        $("#payment_type_id").change(function() {
            $("#payment_mode_id").val("#").change();
            $("#new-row").empty();
            $("#totalRow").val(0);

            $('#tot-terbayar').text(0);
            $('#vat-terbayar').text(0);
            $('#grand-tot-terbayar').text(0);
            $('#grand-tot-terbayar-val').val(0);
        });

        $("#payment_mode_id").change(function() {
            $("#ref_id").empty();
            $("#ref_id").append(`<option value="#">Choose...</option>`);
            var fd = new FormData();
            fd.append('payment_mode_id', $('#payment_mode_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_payment_ref') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].refs;
                    let totRo = o.length;
                    if (totRo > 0) {
                        for (let i = 0; i < totRo; i++) {
                            optionText = o[i].title_ind;
                            optionValue = o[i].id;
                            $("#ref_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    }
                },
            });

            $("#coa_id").empty();
            $("#coa_id").append(`<option value="">Choose...</option>`);
            var fd = new FormData();
            fd.append('customer_id', $('#customer_id option:selected').val());
            fd.append('payment_mode_id', $('#payment_mode_id option:selected').val());
            fd.append('payment_group', ($('#payment_type_id option:selected').val()=='P'?7:14));
            $.ajax({
                url: "{{ url('/disp_bankaccnoforcust') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].bankaccno;
                    let totRo = o.length;
                    if (totRo > 0) {
                        for (let i = 0; i < totRo; i++) {
                            optionText = o[i].coa_name;
                            optionValue = o[i].id;
                            $("#coa_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    }
                },
            });
            if($('#payment_mode_id option:selected').val()==1){
                $('#admin_bank').val(0);
                $('#admin_bank').css('background-color','gray');
                $('input[name="admin_bank"]').attr('readonly', true);
            }
            if($('#payment_mode_id option:selected').val()==2 || $('#payment_mode_id option:selected').val()==3){
                $('#admin_bank').css('background-color','white');
                $('#admin_bank').removeAttr('readonly');
            }
        });

        $("#customer_id").change(function() {
            $("#payment_type_id").val("").change();
            $("#new-row").empty();
            $("#totalRow").val(0);
        });
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
        $("#save-plan").click(function() {
            if(!confirm("Data will be saved to database with PLAN status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#is_draft").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            $('#reference_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#payment_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            if($("#customer_id").val()==='#'){
                alert('Please select a valid customer');
                $("#customer_id").focus();
                return false;
            }
            if($("#payment_type_id").val()===''){
                alert('Please select a valid payment type (NPWP)');
                $("#payment_type_id").focus();
                return false;
            }
            if($("#coa_id").val()===''){
                // if($("#coa_id").val()==='' && $("#payment_mode_id").val()!=3){
                alert('Please select a valid No Rekening');
                $("#coa_id").focus();
                return false;
            }

            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow)+1);
            let vHtml = '<tr id="row'+totalRow+'">'+
                '<th scope="row" style="text-align:right;"><label for="" id="payment_receipt_row_number'+totalRow+'" class="col-form-label">'+rowNo+'.</label></th>'+
                '<td>'+
                '<select onchange="dispTotPrice(this.value, '+totalRow+');" class="form-select single-select" id="invoice_no_'+totalRow+'" name="invoice_no_'+totalRow+'">'+
                '<option value="#">Choose...</option>'+
                '</select>'+
                '</td>'+
                '<td><label id="fk_id_nr_id_'+totalRow+'" class="col-form-label"></label></td>'+
                '<td><label id="fk_date_nr_date_'+totalRow+'" class="col-form-label"></label></td>'+
                '<td><textarea class="form-control" name="desc_'+totalRow+'" id="desc_'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>'+
                '<td style="text-align:right;">'+
                '<label id="total_must_be_paid_'+totalRow+'" class="col-form-label"></label><br/>'+
                '<label id="nr_total_before_vat_lbl_'+totalRow+'" class="col-form-label" style="padding-bottom:0;color:red;"></label>'+
                '</td>'+
                '<td style="text-align:right;">'+
                '<input onkeyup="formatPartPrice($(this));" type="text" class="form-control" style="text-align: right;" id="total_inv_'+totalRow+'" '+
                'name="total_inv_'+totalRow+'" maxlength="25" value="" />'+
                '<input type="hidden" id="total_inv_o_'+totalRow+'" name="total_inv_o_'+totalRow+'" maxlength="25" value="" />'+
                // '<label id="total_inv_lbl_'+totalRow+'" class="col-form-label" style="padding-bottom:0;"></label>'+
                '</td>'+
                '<td style="text-align:right;">'+
                '<label id="remainder_must_be_paid_'+totalRow+'" class="col-form-label">0</label>'+
                '</td>'+
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            $("#invoice_no_"+totalRow).empty();
            var fd = new FormData();
            fd.append('pc_id', 0);
            fd.append('customer_id', $("#customer_id").val());
            fd.append('coa_id', $('#coa_id option:selected').val());
            fd.append('payment_type_id', $('#payment_type_id option:selected').val());
            $.ajax({
                url: "{{ url('/disp_inv_per_do_info') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].invoices;
                    let totRo = o.length;
                    if (totRo > 0) {
                        $("#invoice_no_"+totalRow).append(`<option value="#">Choose...</option>`);
                        for (let i = 0; i < totRo; i++) {
                            optionText = o[i].invoice_no;
                            optionValue = encodeURI(o[i].invoice_no);
                            $("#invoice_no_"+totalRow).append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#payment_receipt_row_number"+i).text()){
                    $("#payment_receipt_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width')?$(this).data('width'):$(this).hasClass(
                    'w-100')?'100%':'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#payment_receipt_row_number"+i).text()){
                    $("#payment_receipt_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            sumTerbayar();
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width')?$(this).data('width'):$(this).hasClass('w-100')?'100%':'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
