<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>OutstandingPurchaseOrder</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 10;
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
                        <th colspan="{{ $totCols }}">OUTSTANDING PURCHASE ORDER</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format(date_create($date),"d/m/Y") }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">SUPPLIER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">PO NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">PARTS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">PARTS NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">ORD QTY</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">HARGA DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">TOTAL DPP({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">ESTIMASI DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#99ff99;">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $supplier_name='';

                        $purchase_orders = \App\Models\Tx_purchase_order::leftJoin('mst_suppliers as ms_sp','tx_purchase_orders.supplier_id','=','ms_sp.id')
                        ->leftJoin('mst_supplier_bank_information as ms_spb','ms_sp.id','=','ms_spb.supplier_id')
                        ->leftJoin('mst_globals as ms_gc','ms_spb.currency_id','=','ms_gc.id')
                        ->leftJoin('userdetails','tx_purchase_orders.created_by','=','userdetails.user_id')
                        ->select(
                            'tx_purchase_orders.id as po_id',
                            'tx_purchase_orders.purchase_no',
                            'tx_purchase_orders.purchase_date',
                            'tx_purchase_orders.est_supply_date',
                            'ms_sp.name as supplier_name',
                            'ms_sp.supplier_code',
                            'ms_sp.supplier_type_id',
                            'ms_gc.string_val as currency_name',
                            'userdetails.initial as user_initial',
                        )
                        ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
                        ->whereExists(function($query){
                            // tampilkan PO hanya jika ADA sisa part outstanding,
                            // yaitu ada part PO aktif yang belum ter-link ke RO aktif+valid.
                            // Cermin logika $isPartial di OrderServerSideController@status.
                            $query->select(\DB::raw(1))
                            ->from('tx_purchase_order_parts as tx_pop')
                            ->whereColumn('tx_pop.order_id','tx_purchase_orders.id')
                            ->where('tx_pop.active','Y')
                            ->whereNotIn('tx_pop.id',function($q1){
                                $q1->select('tx_rop.po_mo_id')
                                ->from('tx_receipt_order_parts as tx_rop')
                                ->whereNotNull('tx_rop.po_mo_id')
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                ->whereRaw("tx_rop.po_mo_no = tx_purchase_orders.purchase_no")
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where([
                                    'tx_rop.active'=>'Y',
                                    'tx_ro.active'=>'Y',
                                ]);
                            });
                        })
                        ->whereRaw('tx_purchase_orders.purchase_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                        ->whereRaw('tx_purchase_orders.purchase_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                        ->whereRaw('tx_purchase_orders.approved_by IS NOT NULL')
                        ->where([
                            'tx_purchase_orders.active'=>'Y',
                            'ms_sp.active'=>'Y',
                            'ms_spb.active'=>'Y',
                        ])
                        ->orderBy('tx_purchase_orders.purchase_date','ASC')
                        ->get();
                    @endphp
                    @foreach ($purchase_orders as $po)
                        @php
                            $supplier_name='';
                            $totDppPerPO=0;
                            $purchase_no='';
                            $purchase_order_parts = \App\Models\Tx_purchase_order_part::leftJoin('mst_parts as msp','tx_purchase_order_parts.part_id','=','msp.id')
                            ->leftJoin('tx_purchase_orders as tx_po','tx_purchase_order_parts.order_id','=','tx_po.id')
                            ->select(
                                'tx_purchase_order_parts.id as pop_id',
                                'tx_purchase_order_parts.qty',
                                'tx_purchase_order_parts.price',
                                'msp.part_number',
                                'msp.part_name',
                                'msp.avg_cost',
                                'tx_po.supplier_type_id',
                            )
                            ->where([
                                'tx_purchase_order_parts.order_id'=>$po->po_id,
                                'tx_purchase_order_parts.active'=>'Y',
                            ])
                            ->orderBy('msp.part_number','ASC')
                            ->get();
                        @endphp
                        @foreach ($purchase_order_parts as $pop)
                            @php
                                $received = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->where('tx_receipt_order_parts.po_mo_id',$pop->pop_id)
                                ->where('tx_receipt_order_parts.po_mo_no',$po->purchase_no)
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where([
                                    'tx_receipt_order_parts.active'=>'Y',
                                    'tx_ro.active'=>'Y',
                                ])
                                ->sum('tx_receipt_order_parts.qty');
                                $sisa = $pop->qty - $received;
                            @endphp
                            @if ($sisa > 0)
                            <tr>
                                <td>{{ ($supplier_name!=$po->supplier_name)?$po->supplier_code.' - '.$po->supplier_name:'' }}</td>
                                <td>{{ ($purchase_no!=$po->purchase_no)?$po->purchase_no:'' }}</td>
                                <td style="text-align: center;">{{ $po->purchase_date }}</td>
                                <td>{{ $pop->part_number }}</td>
                                <td>{{ $pop->part_name }}</td>
                                <td style="text-align: right;">{{ $sisa }}</td>
                                <td style="text-align: right;">{{ number_format($pop->price,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format(($pop->price*$sisa),0,'.','') }}</td>
                                <td style="text-align: center;">{{ $po->est_supply_date }}</td>
                                <td style="text-align: center;">{{ $po->user_initial }}</td>
                            </tr>
                            @php
                                // $totDppPerPO+=($pop->price*$pop->qty*($pop->supplier_type_id==10?$pop->avg_cost:1));
                                $totDppPerPO+=($pop->price*$sisa);
                                $supplier_name=$po->supplier_name;
                                $purchase_no=$po->purchase_no;
                            @endphp
                            @endif
                        @endforeach
                        @if ($totDppPerPO>0)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;font-weight:700;">{!! ($po->supplier_type_id==10?$po->currency_name:'&nbsp;') !!}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totDppPerPO,0,'.','') }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                            </tr>
                        @endif
                    @endforeach

                    @php
                        $purchase_memos = \App\Models\Tx_purchase_memo::leftJoin('mst_suppliers as ms_sp','tx_purchase_memos.supplier_id','=','ms_sp.id')
                        ->leftJoin('mst_supplier_bank_information as ms_spb','ms_sp.id','=','ms_spb.supplier_id')
                        ->leftJoin('mst_globals as ms_gc','ms_spb.currency_id','=','ms_gc.id')
                        ->leftJoin('userdetails','tx_purchase_memos.created_by','=','userdetails.user_id')
                        ->select(
                            'tx_purchase_memos.id as mo_id',
                            'tx_purchase_memos.memo_no',
                            'tx_purchase_memos.memo_date',
                            // 'tx_purchase_memos.supplier_type_id',
                            'ms_sp.name as supplier_name',
                            'ms_sp.supplier_code',
                            'ms_sp.supplier_type_id',
                            'ms_gc.string_val as currency_name',
                            'userdetails.initial as user_initial',
                        )
                        ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%')
                        ->whereExists(function($query){
                            // tampilkan memo hanya jika ADA sisa part outstanding,
                            // yaitu ada part memo aktif yang belum ter-link ke RO aktif+valid.
                            // Pola sama dengan blok PO di atas.
                            $query->select(\DB::raw(1))
                            ->from('tx_purchase_memo_parts as tx_mop')
                            ->whereColumn('tx_mop.memo_id','tx_purchase_memos.id')
                            ->where('tx_mop.active','Y')
                            ->whereNotIn('tx_mop.id',function($q1){
                                $q1->select('tx_rop.po_mo_id')
                                ->from('tx_receipt_order_parts as tx_rop')
                                ->whereNotNull('tx_rop.po_mo_id')
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                ->whereRaw("tx_rop.po_mo_no = tx_purchase_memos.memo_no")
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where([
                                    'tx_rop.active'=>'Y',
                                    'tx_ro.active'=>'Y',
                                ]);
                            });
                        })
                        ->whereRaw('tx_purchase_memos.memo_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                        ->whereRaw('tx_purchase_memos.memo_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                        ->where([
                            'tx_purchase_memos.active'=>'Y',
                            'ms_sp.active'=>'Y',
                            'ms_spb.active'=>'Y',
                        ])
                        ->orderBy('tx_purchase_memos.memo_date','ASC')
                        ->get();
                    @endphp
                    @foreach ($purchase_memos as $po)
                        @php
                            $supplier_name='';
                            $totDppPerMO=0;
                            $memo_no='';
                            $purchase_memo_parts = \App\Models\Tx_purchase_memo_part::leftJoin('mst_parts as msp','tx_purchase_memo_parts.part_id','=','msp.id')
                            ->select(
                                'tx_purchase_memo_parts.id as mop_id',
                                'tx_purchase_memo_parts.qty',
                                'tx_purchase_memo_parts.price',
                                'msp.part_number',
                                'msp.part_name',
                            )
                            ->where([
                                'tx_purchase_memo_parts.memo_id'=>$po->mo_id,
                                'tx_purchase_memo_parts.active'=>'Y',
                            ])
                            ->orderBy('msp.part_number','ASC')
                            ->get();
                        @endphp
                        @foreach ($purchase_memo_parts as $pop)
                            @php
                                $received = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->where('tx_receipt_order_parts.po_mo_id',$pop->mop_id)
                                ->where('tx_receipt_order_parts.po_mo_no',$po->memo_no)
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where([
                                    'tx_receipt_order_parts.active'=>'Y',
                                    'tx_ro.active'=>'Y',
                                ])
                                ->sum('tx_receipt_order_parts.qty');
                                $sisa = $pop->qty - $received;
                            @endphp
                            @if ($sisa > 0)
                            <tr>
                                <td>{{ ($supplier_name!=$po->supplier_name)?$po->supplier_code.' - '.$po->supplier_name:'' }}</td>
                                <td>{{ ($memo_no!=$po->memo_no)?$po->memo_no:'' }}</td>
                                <td style="text-align: center;">{{ $po->memo_date }}</td>
                                <td>{{ $pop->part_number }}</td>
                                <td>{{ $pop->part_name }}</td>
                                <td style="text-align: right;">{{ $sisa }}</td>
                                <td style="text-align: right;">{{ number_format($pop->price,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format(($pop->price*$sisa),0,'.','') }}</td>
                                <td style="text-align: center;">&nbsp;</td>
                                <td style="text-align: center;">{{ $po->user_initial }}</td>
                            </tr>
                            @php
                                $totDppPerMO+=($pop->price*$sisa);
                                $supplier_name=$po->supplier_name;
                                $memo_no=$po->memo_no;
                            @endphp
                            @endif
                        @endforeach
                        @if ($totDppPerMO>0)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;font-weight:700;">{!! ($po->supplier_type_id==10?$po->currency_name:'&nbsp;') !!}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totDppPerMO,0,'.','') }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
