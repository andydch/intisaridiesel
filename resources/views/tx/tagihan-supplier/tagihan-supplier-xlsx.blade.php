<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>TagihanSupplier</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $totCols = 7;
                    $dt_s = explode("-", $date_start);
                    $dt_e = explode("-", $date_end);

                    $startDate = $dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0];
                    $endDate = $dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0];
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">COLLECTION TAGIHAN SUPPLIER</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ str_replace("-", "/", $date_start).' s/d '.str_replace("-", "/", $date_end) }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">Supplier</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">Bank</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TS No</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">Plan Date</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">Total Price VAT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">RO No</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INV No</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PR No</th>
                    </tr>
                    @php
                        $supplier_name = '';
                        $bank_name = '';
                        $tagihan_supplier_no = '';
                        $plan_date = '';
                        $grandtotal_price = 0;
                        $grandtotal_price_real = 0;

                        $qTagihanSuppliers = \App\Models\Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tts', 'tts.id', '=', 'tx_tagihan_supplier_details.tagihan_supplier_id')
                        ->leftJoin('mst_suppliers as msp', 'msp.id', '=', 'tts.supplier_id')
                        ->leftJoin('tx_receipt_orders as tx_ro', 'tx_ro.id', '=', 'tx_tagihan_supplier_details.receipt_order_id')
                        ->leftJoin('mst_globals as gb', 'msp.entity_type_id', '=', 'gb.id')
                        ->leftJoin('tx_purchase_returs as tx_pr', 'tx_ro.id', '=', 'tx_pr.receipt_order_id')
                        ->leftJoin('mst_coas as coa', 'tts.bank_id', '=', 'coa.id')
                        ->select(
                            'tts.tagihan_supplier_no',
                            'tts.tagihan_supplier_date',
                            'tts.grandtotal_price',
                            'msp.name as supplier_name',
                            'msp.supplier_code',
                            'tx_ro.receipt_no',
                            'tx_ro.invoice_no',
                            'gb.title_ind',
                            'tx_pr.purchase_retur_no',
                            'coa.coa_name as bank_name',
                        )
                        ->whereBetween(DB::raw('DATE(tts.tagihan_supplier_date)'), [$startDate, $endDate])
                        // ->whereNotIn('tts.id', function($q){
                        //     $q->select('tagihan_supplier_id')
                        //     ->from('tx_payment_vouchers')
                        //     ->whereRaw('tagihan_supplier_id IS NOT null');
                        // })
                        ->where('tx_ro.branch_id', $branch_id)
                        ->orderBy('msp.name', 'asc')
                        ->orderBy('tts.tagihan_supplier_no', 'asc')
                        ->orderBy('tts.tagihan_supplier_date', 'asc')
                        ->get();
                        // dd($qTagihanSuppliers);
                    @endphp
                    @foreach ($qTagihanSuppliers as $qS)
                        <tr>
                            <td style="border-left: 1px solid black;border-right: 1px solid black;">{{ $supplier_name!=$qS->supplier_name?strtoupper($qS->supplier_code.' - '.$qS->title_ind.' '.$qS->supplier_name):'' }}</td>
                            <td style="border-left: 1px solid black;">{{ $tagihan_supplier_no!=$qS->tagihan_supplier_no?$qS->bank_name:'' }}</td>
                            <td style="text-align: center;">{{ $tagihan_supplier_no!=$qS->tagihan_supplier_no?$qS->tagihan_supplier_no:'' }}</td>
                            @php
                                $planDate = date_create($qS->tagihan_supplier_date);
                            @endphp
                            <td style="text-align: center;">{{ $tagihan_supplier_no!=$qS->tagihan_supplier_no?date_format($planDate, "d/m/Y"):'' }}</td>
                            <td>{{ $grandtotal_price!=$qS->grandtotal_price?number_format($qS->grandtotal_price, 0, ".", ""):'' }}</td>
                            <td style="text-align: center;">{{ $qS->receipt_no }}</td>
                            <td style="text-align: center;">{{ $qS->invoice_no }}</td>
                            <td style="text-align: center;border-right: 1px solid black;">{{ strpos($qS->purchase_retur_no, "Draft")<0?$qS->purchase_retur_no:'' }}</td>
                        </tr>
                        @php
                            if ($grandtotal_price!=$qS->grandtotal_price){
                                $grandtotal_price_real += (float)number_format($qS->grandtotal_price, 0, ".", "");
                            }
                            $supplier_name = $qS->supplier_name;
                            $bank_name = $qS->bank_name;
                            $tagihan_supplier_no = $qS->tagihan_supplier_no;
                            $plan_date = $qS->tagihan_supplier_date;
                            $grandtotal_price = $qS->grandtotal_price;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: center;font-weight: 700;border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;">TOTAL</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="font-weight: 700;border-top: 1px solid black;border-bottom: 1px solid black;">{{ number_format($grandtotal_price_real, 0, ".", "") }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
