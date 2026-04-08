<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SupplierPaymentStatus</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 11;
                    $monthNm = '';
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">SUPPLIER PAYMENT STATUS</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA SUPPLIER</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO INVOICE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INVOICE DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TS PLAN DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PAID DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PAID AMOUNT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALDO ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalDPP=0;
                        $totalVAT=0;
                        $totalPaidAmount=0;
                        $totalSaldo=0;

                        $branches = \App\Models\Mst_branch::when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight:700;border-left:1px solid black;font-weight:700;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $supplier_name = '';
                            $ts_no = '';
                            $saldo_tmp = 0;

                            $suppliers = \App\Models\Mst_supplier::whereIn('id', function($q) use($dt_s, $dt_e, $branch) {
                                $q->select('supplier_id')
                                ->from('tx_receipt_orders')
                                ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                    AND receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where('active','=','Y');
                            })
                            ->where('active','=','Y')
                            ->orderBy('name','ASC')
                            ->get();
                        @endphp
                        @foreach ($suppliers as $supplier)
                            @php
                                $tagihanSuppliers = \App\Models\Tx_tagihan_supplier::leftJoin('tx_tagihan_supplier_details AS tx_tsd', 'tx_tagihan_suppliers.id','=','tx_tsd.tagihan_supplier_id')
                                ->leftJoin('tx_receipt_orders AS tx_ro','tx_tsd.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('tx_payment_voucher_invoices AS tx_pvi','tx_tsd.receipt_order_id','=','tx_pvi.receipt_order_id')
                                ->leftJoin('tx_payment_vouchers AS tx_pv','tx_pvi.payment_voucher_id','=','tx_pv.id')
                                ->select(
                                    'tx_tagihan_suppliers.tagihan_supplier_no',
                                    'tx_tagihan_suppliers.tagihan_supplier_date',
                                    'tx_tagihan_suppliers.grandtotal_price as ts_grandtotal_price',
                                    'tx_ro.invoice_no',
                                    'tx_ro.supplier_type_id',
                                    'tx_ro.total_before_vat',
                                    'tx_ro.total_before_vat_rp',
                                    'tx_ro.total_vat',
                                    'tx_ro.total_vat_rp',
                                    'tx_ro.total_after_vat',
                                    'tx_ro.total_after_vat_rp',
                                    'tx_ro.receipt_date',
                                    'tx_pv.payment_date',
                                    'tx_pv.payment_total_after_vat as pv_total_payment_after_vat',
                                    'tx_pv.approved_by as pv_approved_by',
                                    'tx_pv.is_draft as pv_is_draft',
                                    'tx_pvi.total_payment_after_vat',
                                )
                                ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                    AND tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                // ->whereRaw('tx_tagihan_suppliers.tagihan_supplier_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                //     AND tx_tagihan_suppliers.tagihan_supplier_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where('tx_tagihan_suppliers.supplier_id', '=', $supplier->id)
                                ->where('tx_tagihan_suppliers.active', '=', 'Y')
                                ->where('tx_tsd.active', '=', 'Y')
                                ->where('tx_ro.branch_id', '=', $branch->id)
                                ->where('tx_ro.active', '=', 'Y')
                                ->where('tx_pvi.active', '=', 'Y')
                                // ->whereRaw('tx_pv.approved_by IS NOT null')
                                ->where('tx_pv.active', '=', 'Y')
                                ->get();
                            @endphp
                            if ($tagihanSuppliers){
                                @foreach ($tagihanSuppliers as $ts)
                                    <tr>
                                        <td style="border-left:1px solid black;text-align:left;">{{ ($supplier_name!=$supplier->name)?$supplier->supplier_code.' - '.$supplier->name:'' }}</td>
                                        <td style="text-align:left;">{{ ($ts_no!=$ts->tagihan_supplier_no)?$ts->tagihan_supplier_no:'' }}</td>
                                        <td style="text-align:center;">{{ $ts->invoice_no }}</td>
                                        <td style="text-align:left;">{{ $ts->receipt_date }}</td>
                                        <td style="text-align:right;">{{ $ts->supplier_type_id==10?number_format($ts->total_before_vat_rp,0,'.',''):number_format($ts->total_before_vat,0,'.','') }}</td>
                                        <td style="text-align:right;">{{ $ts->supplier_type_id==10?number_format($ts->total_vat_rp,0,'.',''):number_format($ts->total_vat,0,'.','') }}</td>
                                        <td style="text-align:right;">{{ $ts->supplier_type_id==10?number_format($ts->total_after_vat_rp,0,'.',''):number_format($ts->total_after_vat,0,'.','') }}</td>
                                        <td style="text-align:center;">{{ date_format(date_create($ts->tagihan_supplier_date),"d/m/Y") }}</td>
                                        <td style="text-align:center;">
                                            {{ $ts->pv_approved_by!=null?date_format(date_create($ts->payment_date),"d/m/Y"):'' }}
                                        </td>
                                        <td style="text-align:right;">
                                            {{ $ts->pv_approved_by!=null?number_format($ts->total_payment_after_vat,0,'.',''):'' }}
                                        </td>
                                        @php
                                            $saldo = $ts->ts_grandtotal_price-($ts->pv_approved_by!=null?$ts->pv_total_payment_after_vat:0);
                                        @endphp
                                        <td style="border-right:1px solid black;">
                                            {{ ($saldo!=$saldo_tmp && $ts_no!=$ts->tagihan_supplier_no)?($saldo>0?number_format($saldo,0,'.',''):''):'' }}
                                        </td>
                                    </tr>
                                    @php
                                        $totalDPP += ($ts->supplier_type_id==10?$ts->total_before_vat_rp:$ts->total_before_vat);
                                        $totalVAT += ($ts->supplier_type_id==10?$ts->total_vat_rp:$ts->total_vat);
                                        $totalPaidAmount += ($ts->pv_approved_by!=null?$ts->total_payment_after_vat:0);
                                        $totalSaldo += (($saldo!=$saldo_tmp && $ts_no!=$ts->tagihan_supplier_no)?($saldo>0?$saldo:0):0);
                                        $supplier_name = $supplier->name;
                                        $ts_no = $ts->tagihan_supplier_no;
                                        $saldo_tmp = $saldo;
                                    @endphp
                                @endforeach
                                @if (count($tagihanSuppliers))
                                    <tr>
                                        <td style="border-left:1px solid black;">&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>
                                @endif
                            }
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align:right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalDPP,0,'.','') }}</td>
                        <td style="text-align:right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalVAT,0,'.','') }}</td>
                        <td style="text-align:right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalDPP+$totalVAT,0,'.','') }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align:right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalPaidAmount,0,'.','') }}</td>
                        <td style="text-align:right;font-weight:700;border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalSaldo,0,'.','') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
