<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>INV OVER UNDER STOCK</title>
    </head>
    <body>
        <div class="table-responsive">
            <table id="master-inventory" style="width:1024px;">
                <thead>
                    <tr>
                        <th>{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="13" style="font-weight: bold;font-size: 16px;text-align:center;">REPORT INVENTORY OVER STOCK / UNDER STOCK</th>
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
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <th style="font-weight: bold;">{{ strtoupper($branch->name) }}</th>
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
                            <th style="text-align: center;">{{ date_format(now(), 'd-M-Y') }}</th>
                        </tr>
                        <tr>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">PARTS NO</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">PARTS NAME</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">PARTS TYPE</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">BRAND</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">COST AVG ({{ $qCurrency->string_val }})</th>
                            <th colspan="6" style="text-align: center;background-color:#92d050;font-weight:bold;border:1px solid black;">QTY</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                            <th rowspan="2" style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">POSITION STOCK</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">OH</th>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">SO</th>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">OO</th>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">IT</th>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">MIN</th>
                            <th style="text-align: center;background-color:#eaf1dd;font-weight:bold;border:1px solid black;">MAX</th>
                        </tr>
                        @php
                            $rpts = \App\Models\Tx_qty_part::leftJoin('mst_parts as pr','tx_qty_parts.part_id','=','pr.id')
                            ->leftJoin('mst_branches as br','tx_qty_parts.branch_id','=','br.id')
                            ->leftJoin('mst_globals as bd','pr.brand_id','=','bd.id')
                            ->leftJoin('mst_globals as pr_type','pr.part_type_id','=','pr_type.id')
                            ->select(
                                'br.name as branch_name',
                                'bd.title_ind as brand_name',
                                'pr.part_number',
                                'pr.part_name',
                                'pr.avg_cost',
                                'pr.safety_stock as min_stock',
                                'pr.max_stock',
                                'pr_type.title_ind as part_type_name',
                                'tx_qty_parts.qty as qty_per_branch',
                                'tx_qty_parts.part_id',
                                'tx_qty_parts.branch_id',
                            )
                            // sales order
                            ->addSelect(['sales_order_qty' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0)')
                                ->leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                ->whereNotIn('txso.id',function (\Illuminate\Database\Query\Builder $query) {
                                    $query->select('tx_do_parts.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_do_parts')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                    ->where('tx_do_parts.active','=','Y')
                                    ->where('tx_do.active','=','Y');
                                })
                                ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                ->where('tx_sales_order_parts.active','=','Y')
                                ->where('txso.sales_order_no','NOT LIKE','%Draft%')
                                ->where('txso.need_approval','=','N')
                                ->where('txso.active','=','Y')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty_parts.branch_id)')
                            ])
                            // surat jalan
                            ->addSelect(['surat_jalan_qty' => \App\Models\Tx_surat_jalan_part::selectRaw('IFNULL(SUM(tx_surat_jalan_parts.qty),0)')
                                ->leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
                                ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
                                ->whereNotIn('txsj.id',function (\Illuminate\Database\Query\Builder $query) {
                                    $query->select('tx_do_parts.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
                                    ->where('tx_do_parts.active','=','Y')
                                    ->where('tx_do.active','=','Y');
                                })
                                ->whereColumn('tx_surat_jalan_parts.part_id','tx_qty_parts.part_id')
                                ->where('tx_surat_jalan_parts.active','=','Y')
                                ->where('txsj.surat_jalan_no','NOT LIKE','%Draft%')
                                ->where('txsj.need_approval','=','N')
                                ->where('txsj.active','=','Y')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txsj.branch_id IS null) OR txsj.branch_id=tx_qty_parts.branch_id)')
                            ])
                            // purchase memo
                            ->addSelect(['purchase_memo_qty' => \App\Models\Tx_purchase_memo_part::selectRaw('IFNULL(SUM(tx_purchase_memo_parts.qty),0)')    // total qty dari memo yg aktif
                                ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                                ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                ->whereColumn('tx_purchase_memo_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_purchase_memo_parts.active','=','Y')
                                ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                ->where('tx_memo.active','=','Y')
                            ])
                            // purchase order
                            ->addSelect(['purchase_order_qty' => \App\Models\Tx_purchase_order_part::selectRaw('IFNULL(SUM(tx_purchase_order_parts.qty),0)')  // total qty dari po yg aktif
                                ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                                ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                ->whereColumn('tx_purchase_order_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_purchase_order_parts.active','=','Y')
                                ->where('tx_order.approved_by','<>',null)
                                ->where('tx_order.active','=','Y')
                            ])
                            ->addSelect(['purchase_ro_qty_mo' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg approved
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                ->where('tx_receipt_order_parts.active','=','Y')
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where('tx_ro.active','=','Y')
                                ->whereIn('tx_receipt_order_parts.po_mo_no', function (\Illuminate\Database\Query\Builder $query){
                                    $query->select('tx_memo.memo_no')
                                    ->from('tx_purchase_memos as tx_memo')
                                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                    ->where('tx_memo.active','=','Y');
                                })
                            ])
                            ->addSelect(['purchase_ro_qty_po' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg approved
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                ->where('tx_receipt_order_parts.active','=','Y')
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where('tx_ro.active','=','Y')
                                ->whereIn('tx_receipt_order_parts.po_mo_no', function (\Illuminate\Database\Query\Builder $query){
                                    $query->select('tx_order.purchase_no')
                                    ->from('tx_purchase_orders as tx_order')
                                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                    ->where('tx_order.approved_by','<>',null)
                                    ->where('tx_order.active','=','Y');
                                })
                            ])
                            ->addSelect(['purchase_ro_qty_no_partial_mo' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                ->where('tx_receipt_order_parts.active','=','Y')
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where('tx_ro.active','=','Y')
                                ->whereIn('tx_receipt_order_parts.po_mo_no', function (\Illuminate\Database\Query\Builder $query){
                                    $query->select('tx_memo.memo_no')
                                    ->from('tx_purchase_memos as tx_memo')
                                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                                    ->where('tx_memo.active','=','Y');
                                })
                            ])
                            ->addSelect(['purchase_ro_qty_no_partial_po' => \App\Models\Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                ->whereColumn('tx_receipt_order_parts.part_id','tx_qty_parts.part_id')
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                ->where('tx_receipt_order_parts.active','=','Y')
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->where('tx_ro.active','=','Y')
                                ->whereIn('tx_receipt_order_parts.po_mo_no', function (\Illuminate\Database\Query\Builder $query){
                                    $query->select('tx_order.purchase_no')
                                    ->from('tx_purchase_orders as tx_order')
                                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                                    ->where('tx_order.approved_by','<>',null)
                                    ->where('tx_order.active','=','Y');
                                })
                            ])
                            // in transit
                            ->addSelect(['in_transit_qty' => \App\Models\Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                                ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
                                ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
                                ->where('tx_stock_transfer_parts.active','=','Y')
                                ->where('tx_stock.approved_by','<>',null)
                                ->where('tx_stock.received_by','=',null)
                                ->where('tx_stock.active','=','Y')
                            ])
                            // final price terbaru
                            ->addSelect(['last_final_price' => \App\Models\Tx_sales_order_part::selectRaw('IFNULL(tx_sales_order_parts.price,0)')
                                ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                                ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
                                // ---
                                // gunakan kode cabang user ketika cabang SO kosong
                                // jika cabang SO ada maka gunakan kode cabang SO
                                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty_parts.branch_id)')
                                // ---
                                ->where('tx_sales_order_parts.active','=','Y')
                                ->where('txso.active','=','Y')
                                ->orderBy('txso.created_at','DESC')     // ambil harga terbaru dari
                                ->limit(1)                              // data di baris pertama
                            ])
                            ->where('pr.active','=','Y')
                            ->where('tx_qty_parts.branch_id','=',$branch->id)
                            ->whereRaw('IFNULL(pr.safety_stock,0)>0')
                            ->whereRaw('IFNULL(pr.max_stock,0)>0')
                            ->whereRaw('(tx_qty_parts.qty<=pr.safety_stock OR tx_qty_parts.qty>=pr.max_stock)')
                            ->where('br.active','=','Y')
                            ->orderBy('br.name','ASC')
                            ->orderBy('bd.title_ind','ASC')
                            ->orderBy('pr.part_number','ASC')
                            ->get();
                            // dd($rpts->toSql());
                        @endphp
                        @foreach ($rpts as $rpt)
                            @php
                                $oo = ($rpt->purchase_memo_qty+$rpt->purchase_order_qty)-($rpt->purchase_ro_qty_mo+$rpt->purchase_ro_qty_po+$rpt->purchase_ro_qty_no_partial_mo+$rpt->purchase_ro_qty_no_partial_po);
                                // $avail_qty = $rpt->qty_per_branch;
                                $avail_qty = $rpt->qty_per_branch+$oo+$rpt->in_transit_qty-($rpt->sales_order_qty+$rpt->surat_jalan_qty);
                            @endphp
                            @if (((is_null($rpt->min_stock)?0:$rpt->min_stock)>$avail_qty && (is_null($rpt->max_stock)?0:$rpt->max_stock)>$avail_qty) || ((is_null($rpt->min_stock)?0:$rpt->min_stock)<$avail_qty && (is_null($rpt->max_stock)?0:$rpt->max_stock)<$avail_qty))
                                <tr>
                                    <td style="border:1px solid black;">
                                        @php
                                            $partNumber = $rpt->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ $partNumber }}
                                    </td>
                                    <td style="border:1px solid black;">{{ $rpt->part_name }}</td>
                                    <td style="border:1px solid black;">{{ $rpt->part_type_name }}</td>
                                    <td style="border:1px solid black;">{{ $rpt->brand_name }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($rpt->avg_cost,0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $rpt->qty_per_branch }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ ($rpt->sales_order_qty+$rpt->surat_jalan_qty) }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $oo }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $rpt->in_transit_qty }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ (is_null($rpt->min_stock)?0:$rpt->min_stock) }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ (is_null($rpt->max_stock)?0:$rpt->max_stock) }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format((($rpt->qty_per_branch*$rpt->avg_cost)+($rpt->in_transit_qty*$rpt->avg_cost)),0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">
                                        @if ((is_null($rpt->min_stock)?0:$rpt->min_stock)>$avail_qty && (is_null($rpt->max_stock)?0:$rpt->max_stock)>$avail_qty)
                                            {{ 'Under' }}
                                        @endif
                                        @if ((is_null($rpt->min_stock)?0:$rpt->min_stock)<$avail_qty && (is_null($rpt->max_stock)?0:$rpt->max_stock)<$avail_qty)
                                            {{ 'Over' }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
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
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
