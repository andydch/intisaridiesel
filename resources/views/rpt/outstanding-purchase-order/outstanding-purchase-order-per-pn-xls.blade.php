<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Outstanding PO</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                <thead>
                    <tr>
                        <th colspan="11">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="11">OUTSTANDING PURCHASE ORDER PER P/N</th>
                    </tr>
                    <tr>
                        <th colspan="11">{{ $year_id }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();

                        $grandtotal = 0;
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th style="text-align: center;">{{ date_format(now(), 'd-M-Y') }}</th>
                        </tr>
                        <tr>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">PARTS NO</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">PARTS NAME</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">PARTS TYPE</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">NAMA SUPPLIER</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">NO PO/MO</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">DATE</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">QTY ORD</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">HARGA DPP ({{ $qCurrency->string_val }})</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">QTY OH</th>
                            <th style="background-color: #eaf1dd;font-weight:bold;border:1px solid black;">ESTIMASI SUPPLY</th>
                        </tr>
                        <tr>
                            <th colspan="11" style="font-weight: bold;border:1px solid black;">CABANG: {{ strtoupper($branch->name) }}</th>
                        </tr>
                        @php
                            $subtotal = 0;

                            // purchase order
                            $po_parts = \App\Models\Tx_purchase_order_part::leftJoin('tx_purchase_orders as tx_po','tx_purchase_order_parts.order_id','=','tx_po.id')
                            ->leftJoin('mst_parts as msp','tx_purchase_order_parts.part_id','=','msp.id')
                            ->leftJoin('mst_suppliers as ms_sup','tx_po.supplier_id','=','ms_sup.id')
                            ->leftJoin('mst_globals as pr_type','msp.part_type_id','=','pr_type.id')
                            ->select(
                                'msp.part_number',
                                'msp.part_name',
                                'pr_type.title_ind as part_type_name',
                                'ms_sup.name as supplier_name',
                                'tx_po.purchase_no',
                                'tx_po.purchase_date',
                                'tx_purchase_order_parts.qty as qty_ord',
                                'tx_purchase_order_parts.price as price_dpp',
                                'tx_po.est_supply_date',
                            )
                            ->addSelect([
                                'qty_ro' => \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                ->selectRaw('SUM(tx_receipt_order_parts.qty)')
                                ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_receipt_order_parts.po_mo_no=tx_po.purchase_no AND tx_receipt_order_parts.part_id=tx_purchase_order_parts.id')
                                ->where([
                                    'tx_receipt_order_parts.active' => 'Y',
                                    'txro.active' => 'Y',
                                ])
                            ])
                            ->addSelect([
                                'qty_now' => \App\Models\Tx_qty_part::select('qty')
                                ->whereRaw('tx_qty_parts.part_id=tx_purchase_order_parts.part_id AND tx_qty_parts.branch_id=tx_po.branch_id')
                            ])
                            ->whereNotIn('order_id', function($q){
                                $q->select('po_mo_id')
                                ->from('tx_receipt_order_parts')
                                ->leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                ->whereColumn('po_mo_no','tx_po.purchase_no')
                                ->whereColumn('part_id','tx_purchase_order_parts.part_id')
                                ->where([
                                    'tx_receipt_order_parts.is_partial_received' => 'N',
                                    'tx_receipt_order_parts.active' => 'Y',
                                ]);
                            })
                            ->where([
                                'tx_purchase_order_parts.active' => 'Y',
                                'tx_po.branch_id' => $branch->id,
                                'tx_po.active' => 'Y',
                            ])
                            ->orderBy('msp.part_number','ASC')
                            ->get();
                        @endphp
                        @foreach ($po_parts as $part)
                            @if (($part->qty_ord-$part->qty_ro)>0)
                                <tr>
                                    <td style="border:1px solid black;">
                                        @php
                                            $partNumber = $part->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ strtoupper($partNumber) }}
                                    </td>
                                    <td style="border:1px solid black;">{{ strtoupper($part->part_name) }}</td>
                                    <td style="border:1px solid black;">{{ $part->part_type_name }}</td>
                                    <td style="border:1px solid black;">{{ strtoupper($part->supplier_name) }}</td>
                                    <td style="border:1px solid black;">{{ $part->purchase_no }}</td>
                                    <td style="border:1px solid black;">{{ date_format(date_create($part->purchase_date),"d/m/Y") }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $part->qty_ord-$part->qty_ro }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($part->price_dpp,0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format(($part->price_dpp*$part->qty_ord),0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $part->qty_now }}</td>
                                    <td style="border:1px solid black;">{{ (!is_null($part->est_supply_date)?date_format(date_create($part->est_supply_date),"d/m/Y"):'') }}</td>
                                </tr>
                                @php
                                    $subtotal+=($part->price_dpp*$part->qty_ord);
                                @endphp
                            @endif
                        @endforeach

                        @php
                            // purchase memo
                            $mo_parts = \App\Models\Tx_purchase_memo_part::leftJoin('tx_purchase_memos as tx_mo','tx_purchase_memo_parts.memo_id','=','tx_mo.id')
                            ->leftJoin('mst_parts as msp','tx_purchase_memo_parts.part_id','=','msp.id')
                            ->leftJoin('mst_suppliers as ms_sup','tx_mo.supplier_id','=','ms_sup.id')
                            ->leftJoin('mst_globals as pr_type','msp.part_type_id','=','pr_type.id')
                            ->select(
                                'msp.part_number',
                                'msp.part_name',
                                'pr_type.title_ind as part_type_name',
                                'ms_sup.name as supplier_name',
                                'tx_mo.memo_no',
                                'tx_mo.memo_date',
                                'tx_purchase_memo_parts.qty as qty_ord',
                                'tx_purchase_memo_parts.price as price_dpp',
                            )
                            ->addSelect([
                                'qty_ro' => \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                ->selectRaw('SUM(tx_receipt_order_parts.qty)')
                                ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_receipt_order_parts.po_mo_no=tx_mo.memo_no AND tx_receipt_order_parts.part_id=tx_purchase_memo_parts.id')
                                ->where([
                                    'tx_receipt_order_parts.active' => 'Y',
                                    'txro.active' => 'Y',
                                ])
                            ])
                            ->addSelect([
                                'qty_now' => \App\Models\Tx_qty_part::select('qty')
                                ->whereRaw('tx_qty_parts.part_id=tx_purchase_memo_parts.part_id AND tx_qty_parts.branch_id=tx_mo.branch_id')
                            ])
                            ->whereNotIn('memo_id', function($q){
                                $q->select('po_mo_id')
                                ->from('tx_receipt_order_parts')
                                ->leftJoin('tx_receipt_orders AS txro','tx_receipt_order_parts.receipt_order_id','=','txro.id')
                                ->where('txro.receipt_no','NOT LIKE','%Draft%')
                                ->whereColumn('po_mo_no','tx_mo.memo_no')
                                ->whereColumn('part_id','tx_purchase_memo_parts.part_id')
                                ->where([
                                    'tx_receipt_order_parts.is_partial_received' => 'N',
                                    'tx_receipt_order_parts.active' => 'Y',
                                ]);
                            })
                            ->where([
                                'tx_purchase_memo_parts.active' => 'Y',
                                'tx_mo.branch_id' => $branch->id,
                                'tx_mo.active' => 'Y',
                            ])
                            ->orderBy('msp.part_number','ASC')
                            ->get();
                        @endphp
                        @foreach ($mo_parts as $part)
                            @if (($part->qty_ord-$part->qty_ro)>0)
                                <tr>
                                    <td style="border:1px solid black;">
                                        @php
                                            $partNumber = $part->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ strtoupper($partNumber) }}
                                    </td>
                                    <td style="border:1px solid black;">{{ strtoupper($part->part_name) }}</td>
                                    <td style="border:1px solid black;">{{ $part->part_type_name }}</td>
                                    <td style="border:1px solid black;">{{ strtoupper($part->supplier_name) }}</td>
                                    <td style="border:1px solid black;">{{ $part->memo_no }}</td>
                                    <td style="border:1px solid black;">{{ date_format(date_create($part->memo_date),"d/m/Y") }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $part->qty_ord-$part->qty_ro }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($part->price_dpp,0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format(($part->price_dpp*$part->qty_ord),0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $part->qty_now }}</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                </tr>
                                @php
                                    $subtotal+=($part->price_dpp*$part->qty_ord);
                                @endphp
                            @endif
                        @endforeach
                        <tr>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="font-weight:bold;border:1px solid black;">TOTAL</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($subtotal,0,'.','') }}</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                            <th style="border:1px solid black;">&nbsp;</th>
                        </tr>
                        @php
                            $grandtotal+=$subtotal;
                        @endphp
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                    @endforeach
                    <tr>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="font-weight:bold;border:1px solid black;">GRAND TOTAL</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandtotal,0,'.','') }}</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                        <th style="border:1px solid black;">&nbsp;</th>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
