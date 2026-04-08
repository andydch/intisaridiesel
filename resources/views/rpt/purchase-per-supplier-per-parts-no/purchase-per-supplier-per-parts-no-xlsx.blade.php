<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PurchPerSupplierPerPartsNo</title>
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
                        <th colspan="{{ $totCols }}">PURCHASE PER SUPPLIER PER PARTS NO</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA SUPPLIER</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INV NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">RO NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">QTY</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL HARGA ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalDPP=0;
                        $supplier_name='';
                        $part_no='';
                        $totHargaAll=0;

                        $suppliers = \App\Models\Mst_supplier::when($supplier_id!=9999, function($q) use($supplier_id){
                            $q->where('id', $supplier_id);
                        })
                        ->where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($suppliers as $supplier)
                        @php
                            $totHargaPerSupplier=0;
                            $receipt_order_parts = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                            ->leftJoin('mst_parts as msp','tx_receipt_order_parts.part_id','=','msp.id')
                            ->leftJoin('mst_suppliers as mssp','tx_ro.supplier_id','=','mssp.id')
                            ->leftJoin('mst_branches as msb','tx_ro.branch_id','=','msb.id')
                            ->leftJoin('mst_globals as ett_type','mssp.entity_type_id','=','ett_type.id')
                            ->select(
                                'tx_receipt_order_parts.qty as part_qty',
                                'tx_receipt_order_parts.part_id',
                                'tx_receipt_order_parts.final_cost',
                                'tx_receipt_order_parts.final_fob',
                                'tx_receipt_order_parts.total_fob_price',
                                'tx_receipt_order_parts.total_price',
                                'tx_receipt_order_parts.po_mo_no',
                                'tx_ro.invoice_no',
                                'tx_ro.receipt_no',
                                'tx_ro.supplier_type_id',
                                'tx_ro.id as receipt_order_id',
                                'tx_ro.total_before_vat',
                                'tx_ro.total_before_vat_rp',
                                'tx_ro.total_vat',
                                'tx_ro.total_vat_rp',
                                'tx_ro.total_after_vat',
                                'tx_ro.total_after_vat_rp',
                                'tx_ro.exchange_rate',
                                'tx_ro.exc_rate_for_vat',
                                // 'mssp.name as supplier_name',
                                'msp.part_number',
                                'msp.part_name',
                                'msb.initial as branch_initial',
                            )
                            ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mssp.supplier_code,\' - \',mssp.name),
                                CONCAT(mssp.supplier_code,\' - \',ett_type.title_ind,\' \',mssp.name)) as supplier_name')
                            ->where('tx_receipt_order_parts.active', 'Y')
                            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('tx_ro.supplier_id', $supplier->id)
                            ->where('tx_ro.active', 'Y')
                            ->when($branch_id!=9999, function($q) use($branch_id){
                                $q->where('msb.id', $branch_id);
                            })
                            ->orderBy('msp.part_number','ASC')
                            ->get();
                        @endphp
                        @if ($receipt_order_parts)
                            @foreach ($receipt_order_parts as $rop)
                                <tr>
                                    <td style="border-left:1px solid black;">{{ ($supplier_name!=$rop->supplier_name)?$rop->supplier_name:'' }}</td>
                                    <td>
                                        @php
                                            $partNumber = $rop->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ ($part_no!=$rop->part_number)?$partNumber:'' }}
                                    </td>
                                    <td>{{ ($part_no!=$rop->part_number)?$rop->part_name:'' }}</td>
                                    <td style="text-align: center;">{{ $rop->invoice_no }}</td>
                                    <td style="text-align: center;">{{ $rop->receipt_no }}</td>
                                    <td style="text-align: right;">{{ $rop->part_qty }}</td>
                                    @php
                                        // $vatPerPart = ($rop->total_price/($rop->supplier_type_id==10?($rop->total_before_vat*$rop->exchange_rate):$rop->total_before_vat))*100;
                                        // $vatPerPart = $vatPerPart*$rop->total_vat/100;
                                        $vatPerPart = ($rop->total_price/($rop->supplier_type_id==10?$rop->total_before_vat_rp:$rop->total_before_vat))*100;
                                        $vatPerPart = $vatPerPart*($rop->supplier_type_id==10?$rop->total_vat_rp:$rop->total_vat)/100;
                                    @endphp
                                    <td style="text-align: right;">{{ number_format($rop->final_cost,0,'.','') }}</td>
                                    <td style="text-align: right;">{{ number_format($vatPerPart,0,'.','') }}</td>
                                    <td style="text-align: right;">{{ number_format($rop->total_price,0,'.','') }}</td>
                                    <td style="text-align: right;">{{ number_format($rop->total_price+$vatPerPart,0,'.','') }}</td>
                                    <td style="text-align: center;border-right:1px solid black;border-left:1px solid black;">{{ $rop->branch_initial }}</td>
                                </tr>
                                @php
                                    $totHargaPerSupplier+=round($rop->total_price+$vatPerPart);
                                    $supplier_name=$rop->supplier_name;
                                    $part_no=$rop->part_number;

                                    $purchase_retur_parts = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                    ->leftJoin('mst_parts as msp','tx_purchase_retur_parts.part_id','=','msp.id')
                                    ->leftJoin('mst_suppliers as mssp','tx_pr.supplier_id','=','mssp.id')
                                    ->leftJoin('mst_branches as msb','tx_pr.branch_id','=','msb.id')
                                    ->leftJoin('mst_globals as ett_type','mssp.entity_type_id','=','ett_type.id')
                                    ->select(
                                        'tx_purchase_retur_parts.qty_retur as part_qty',
                                        'tx_purchase_retur_parts.part_id',
                                        'tx_purchase_retur_parts.final_cost',
                                        'tx_pr.purchase_retur_no',
                                        'tx_pr.total_before_vat',
                                        'tx_pr.total_after_vat',
                                        'tx_pr.vat_val',
                                        // 'mssp.name as supplier_name',
                                        'msp.part_number',
                                        'msp.part_name',
                                        'msb.initial as branch_initial',
                                    )
                                    ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                        CONCAT(mssp.supplier_code,\' - \',mssp.name),
                                        CONCAT(mssp.supplier_code,\' - \',ett_type.title_ind,\' \',mssp.name)) as supplier_name')
                                    ->where([
                                        'tx_purchase_retur_parts.part_id'=>$rop->part_id,
                                        'tx_purchase_retur_parts.active'=>'Y',
                                        'tx_pr.receipt_order_id'=>$rop->receipt_order_id,
                                    ])
                                    ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('tx_pr.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_pr.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_pr.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_pr.supplier_id'=>$supplier->id,
                                        'tx_pr.active'=>'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($purchase_retur_parts as $prp)
                                    <tr>
                                        <td style="border-left:1px solid black;">{{ ($supplier_name!=$prp->supplier_name)?$prp->supplier_name:'' }}</td>
                                        <td>
                                            @php
                                                $partNumber = $prp->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            {{ ($part_no!=$prp->part_number)?$partNumber:'' }}
                                        </td>
                                        <td>{{ ($part_no!=$prp->part_number)?$prp->part_name:'' }}</td>
                                        <td style="text-align: center;">&nbsp;</td>
                                        <td style="text-align: center;color:red;">{{ $prp->purchase_retur_no }}</td>
                                        <td style="text-align: right;color:red;">-{{ $prp->part_qty }}</td>
                                        <td style="text-align: right;color:red;">{{ number_format($prp->final_cost,0,'.','') }}</td>
                                        @php
                                            $vat_per_part = ($prp->vat_val>0?(($prp->final_cost*$prp->vat_val)/100):0);
                                            $total_price = $prp->vat_val>0?
                                                ((($prp->part_qty*$prp->final_cost)+((($prp->part_qty*$prp->final_cost)*$prp->vat_val)/100))*-1):
                                                (($prp->part_qty*$prp->final_cost)*-1);
                                        @endphp
                                        <td style="text-align: right;color:red;">{{ number_format($vat_per_part*-1,0,'.','') }}</td>
                                        <td style="text-align: right;color:red;">{{ number_format($total_price,0,'.','') }}</td>
                                        <td style="text-align: right;color:red;">{{ number_format($total_price,0,'.','') }}</td>
                                        <td style="text-align: center;border-right:1px solid black;border-left:1px solid black;">{{ $prp->branch_initial }}</td>
                                    </tr>
                                    @php
                                        $totHargaPerSupplier+=round($total_price);
                                    @endphp
                                @endforeach
                            @endforeach
                            @php
                                $purchase_retur_parts = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                ->leftJoin('tx_receipt_orders as tx_ro','tx_pr.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('mst_parts as msp','tx_purchase_retur_parts.part_id','=','msp.id')
                                ->leftJoin('mst_suppliers as mssp','tx_pr.supplier_id','=','mssp.id')
                                ->leftJoin('mst_branches as msb','tx_pr.branch_id','=','msb.id')
                                ->leftJoin('mst_globals as ett_type','mssp.entity_type_id','=','ett_type.id')
                                ->select(
                                    'tx_purchase_retur_parts.qty_retur as part_qty',
                                    'tx_purchase_retur_parts.part_id',
                                    'tx_purchase_retur_parts.final_cost',
                                    'tx_pr.purchase_retur_no',
                                    'tx_pr.total_before_vat',
                                    'tx_pr.total_after_vat',
                                    'tx_pr.vat_val',
                                    'tx_ro.po_or_pm_no',
                                    // 'mssp.name as supplier_name',
                                    'msp.part_number',
                                    'msp.part_name',
                                    'msb.initial as branch_initial',
                                )
                                ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                    CONCAT(mssp.supplier_code,\' - \',mssp.name),
                                    CONCAT(mssp.supplier_code,\' - \',ett_type.title_ind,\' \',mssp.name)) as supplier_name')
                                ->where([
                                    'tx_purchase_retur_parts.active'=>'Y',
                                    'tx_pr.supplier_id'=>$supplier->id,
                                    'tx_pr.active'=>'Y',
                                ])
                                ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_pr.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_pr.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_pr.approved_by IS NOT NULL')
                                ->whereRaw('tx_ro.receipt_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->when($branch_id!=9999, function($q) use($branch_id){
                                    $q->where('msb.id', $branch_id);
                                })
                                ->orderBy('msp.part_number','ASC')
                                ->get();
                            @endphp
                            @foreach ($purchase_retur_parts as $prp)
                                <tr>
                                    <td style="border-left:1px solid black;">{{ ($supplier_name!=$prp->supplier_name)?$prp->supplier_name:'' }}</td>
                                    <td>
                                        @php
                                            $partNumber = $prp->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ ($part_no!=$prp->part_number)?$partNumber:'' }}
                                    </td>
                                    <td>{{ ($part_no!=$prp->part_number)?$prp->part_name:'' }}</td>
                                    <td style="text-align: center;">&nbsp;</td>
                                    <td style="text-align: center;color:red;">{{ $prp->purchase_retur_no }}</td>
                                    <td style="text-align: right;color:red;">-{{ $prp->part_qty }}</td>
                                    <td style="text-align: right;color:red;">{{ number_format($prp->final_cost,0,'.','') }}</td>
                                    @php
                                        $vat_per_part = ($prp->vat_val>0?(($prp->final_cost*$prp->vat_val)/100):0);
                                        $total_price = $prp->vat_val>0?
                                            ((($prp->part_qty*$prp->final_cost)+((($prp->part_qty*$prp->final_cost)*$prp->vat_val)/100))*-1):
                                            (($prp->part_qty*$prp->final_cost)*-1);
                                    @endphp
                                    <td style="text-align: right;color:red;">{{ number_format($vat_per_part*-1,0,'.','') }}</td>
                                    <td style="text-align: right;color:red;">{{ number_format($total_price,0,'.','') }}</td>
                                    <td style="text-align: right;color:red;">{{ number_format($total_price,0,'.','') }}</td>
                                    <td style="text-align: center;border-right:1px solid black;border-left:1px solid black;">{{ $prp->branch_initial }}</td>
                                </tr>
                                @php
                                    $totHargaPerSupplier += round($total_price);
                                @endphp
                            @endforeach
                            @if ($totHargaPerSupplier>0)
                                <tr>
                                    <td style="border-left:1px solid black;">&nbsp;</td>
                                    <td style="text-align: center;font-weight:700;">SUB TOTAL</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totHargaPerSupplier,0,'.','') }}</td>
                                    <td style="border-right:1px solid black;border-left:1px solid black;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="border-left:1px solid black;">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="">&nbsp;</td>
                                    <td style="border-right:1px solid black;border-left:1px solid black;">&nbsp;</td>
                                </tr>
                            @endif
                        @endif
                        @php
                            $totHargaAll+=$totHargaPerSupplier;
                        @endphp
                    @endforeach
                    @if ($totHargaAll>0)
                        <tr>
                            <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                            <td style="text-align: center;font-weight:700;border-bottom:1px solid black;">TOTAL</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="text-align: right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totHargaAll,0,'.','') }}</td>
                            <td style="border-right:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">&nbsp;</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
