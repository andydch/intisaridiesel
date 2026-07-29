<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Summary</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $totCols = 6;
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
                        <th colspan="{{ $totCols }}">SUMMARY COLLECTION TAGIHAN SUPPLIER</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE RO:&nbsp;{{ str_replace("-", "/", $date_start).' s/d '.str_replace("-", "/", $date_end) }}</th>
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
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">Status</th>
                    </tr>
                    @php
                        $supplier_name = '';
                        $bank_name = '';
                        $tagihan_supplier_no = '';
                        $plan_date = '';
                        $grandtotal_price = 0;
                        $grandtotal_price_real = 0;

                        $qTagihanSuppliers = \App\Models\Tx_tagihan_supplier::leftJoin('mst_suppliers as msp', 'msp.id', '=', 'tx_tagihan_suppliers.supplier_id')
                        ->leftJoin('mst_globals as gb', 'msp.entity_type_id', '=', 'gb.id')
                        ->leftJoin('mst_coas as coa', 'tx_tagihan_suppliers.bank_id', '=', 'coa.id')
                        ->select(
                            'tx_tagihan_suppliers.tagihan_supplier_no',
                            'tx_tagihan_suppliers.tagihan_supplier_date',
                            'tx_tagihan_suppliers.grandtotal_price',
                            'msp.name as supplier_name',
                            'msp.supplier_code',
                            'gb.title_ind',
                            'coa.coa_name as bank_name',
                        )
                        ->addSelect([
                            'status' => \App\Models\Tx_payment_voucher::selectRaw('CASE 
                                WHEN is_full_payment=\'N\' THEN \'Partial\' 
                                WHEN approved_by IS null AND is_draft=\'N\' THEN \'PV\'
                                WHEN approved_by IS NOT null THEN \'Paid\' 
                                ELSE \'Created\' 
                                END AS status')
                                ->whereColumn('tagihan_supplier_id', 'tx_tagihan_suppliers.id')
                                ->latest()
                                ->take(1)
                        ])
                        ->whereIn('tx_tagihan_suppliers.id', function($qTSd) use($startDate, $endDate, $branch_id){
                            $qTSd->select('tx_tsi.tagihan_supplier_id')
                            ->from('tx_tagihan_supplier_details AS tx_tsi')
                            ->leftJoin('tx_receipt_orders AS tx_ro', 'tx_ro.id', '=', 'tx_tsi.receipt_order_id')
                            ->where('tx_tsi.active', 'Y')
                            ->where('tx_ro.branch_id', $branch_id)
                            ->whereBetween(DB::raw('DATE(tx_ro.receipt_date)'), [$startDate, $endDate])
                            ->where('tx_ro.active', 'Y');
                        })
                        ->where('tx_tagihan_suppliers.active', 'Y')
                        ->orderBy('msp.name', 'asc')
                        ->orderByDesc(function($qTSd) use($startDate, $endDate, $branch_id){
                            $qTSd->select('tx_ro.receipt_date')
                            ->from('tx_tagihan_supplier_details AS tx_tsi')
                            ->leftJoin('tx_receipt_orders AS tx_ro', 'tx_ro.id', '=', 'tx_tsi.receipt_order_id')
                            ->where('tx_tsi.active', 'Y')
                            ->where('tx_ro.branch_id', $branch_id)
                            ->whereBetween(DB::raw('DATE(tx_ro.receipt_date)'), [$startDate, $endDate])
                            ->where('tx_ro.active', 'Y')
                            ->take(1);
                        })
                        ->orderBy('tx_tagihan_suppliers.tagihan_supplier_no', 'asc')
                        ->orderBy('coa.coa_name', 'asc')
                        ->get();
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
                            <td style="text-align: center;border-right: 1px solid black;">{{ $qS->status!=null?$qS->status:'Created' }}</td>
                        </tr>
                        @php
                            // if ($grandtotal_price!=$qS->grandtotal_price){
                                $grandtotal_price_real += (float)number_format($qS->grandtotal_price, 0, ".", "");
                            // }
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
