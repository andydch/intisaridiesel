<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PurchasePerSupplierPerBranch</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 9;
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
                        <th colspan="{{ $totCols }}">PURCHASE PER SUPPLIER PER BRANCH</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA SUPPLIER</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INV NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">RO NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO PO/MO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX INV NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalDPP = 0;
                        $totalVAT = 0;

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
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $totalPerBranchDPP = 0;
                            $totalPerBranchVAT = 0;
                            $supplier_name='';
                            $suppliers = \App\Models\Mst_supplier::leftJoin('mst_globals as ett_type','mst_suppliers.entity_type_id','=','ett_type.id')
                            ->select(
                                'mst_suppliers.id as supplier_id',
                            )
                            ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mst_suppliers.supplier_code,\' - \',mst_suppliers.name),
                                CONCAT(mst_suppliers.supplier_code,\' - \',ett_type.title_ind,\' \',mst_suppliers.name)) as supplier_name')
                            ->when($supplier_id!=0, function($q) use($supplier_id){
                                $q->where('mst_suppliers.id', '=', $supplier_id);
                            })
                            ->where('mst_suppliers.active','=','Y')
                            ->orderBy('mst_suppliers.name','ASC')
                            ->get();
                        @endphp
                        @foreach ($suppliers as $supplier)
                            @php
                                $totDPPperSupplier = 0;
                                $totPPNperSupplier = 0;
                                $receipt_orders = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where([
                                    'supplier_id'=>$supplier->supplier_id,
                                    'branch_id'=>$branch->id,
                                    'active'=>'Y',
                                ])
                                ->orderBy('receipt_date','ASC');
                            @endphp
                            @foreach ($receipt_orders->get() as $ro)
                                @php
                                    $dpp_last = $ro->total_before_vat_rp==0?$ro->total_before_vat:$ro->total_before_vat_rp;
                                    $vat_last = $ro->total_vat_rp==0?$ro->total_vat:$ro->total_vat_rp;
                                @endphp
                                <tr>
                                    <td style="border-left:1px solid black;">{{ ($supplier_name!=$supplier->supplier_name)?$supplier->supplier_name:'' }}</td>
                                    <td style="text-align:center;">{{ date_format(date_create($ro->receipt_date),"d/m/Y") }}</td>
                                    <td style="text-align:left;">{!! $ro->invoice_no !!}</td>
                                    <td style="text-align:center;">{{ $ro->receipt_no }}</td>
                                    <td style="text-align:center;">{!! str_replace(",","<br/>",substr($ro->po_or_pm_no,1,strlen($ro->po_or_pm_no))) !!}</td>
                                    <td style="text-align:right;">{{ number_format($dpp_last,0,'.','') }}</td>
                                    <td style="text-align:right;">{{ number_format($vat_last,0,'.','') }}</td>
                                    <td style="text-align:right;">{{ number_format($dpp_last+$vat_last,0,'.','') }}</td>
                                    <td>&nbsp;</td>
                                    <td style="border-right:1px solid black;">{{ $ro->createdBy->userDetail->initial }}</td>
                                </tr>
                                @php
                                    $totalDPP += ($ro->total_before_vat_rp==0?$ro->total_before_vat:$ro->total_before_vat_rp);
                                    $totDPPperSupplier += ($ro->total_before_vat_rp==0?$ro->total_before_vat:$ro->total_before_vat_rp);
                                    $totalVAT += $vat_last;
                                    $totPPNperSupplier += $vat_last;
                                    $supplier_name = $supplier->supplier_name;

                                    $purchase_returs = \App\Models\Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where([
                                        'receipt_order_id'=>$ro->id,
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('purchase_retur_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($purchase_returs as $pr)
                                    <tr>
                                        <td style="color:red;border-left:1px solid black;">{{ ($supplier_name!=$supplier->supplier_name)?$supplier->supplier_name:'' }}</td>
                                        <td style="color:red;text-align:center;">{{ date_format(date_create($pr->purchase_retur_date),"d/m/Y") }}</td>
                                        <td style="color:red;">&nbsp;</td>
                                        <td style="color:red;text-align:center;">{{ $pr->purchase_retur_no}}</td>
                                        <td style="color:red;text-align:center;">{!! str_replace(",","<br/>",substr($ro->po_or_pm_no,1,strlen($ro->po_or_pm_no))) !!}</td>
                                        <td style="color:red;text-align:right;">-{{ number_format($pr->total_before_vat,0,'.','') }}</td>
                                        @php
                                            $vat_val = $pr->total_after_vat-$pr->total_before_vat;
                                        @endphp
                                        <td style="color:red;text-align:right;">-{{ number_format($vat_val,0,'.','') }}</td>
                                        <td style="color:red;text-align:right;">-{{ number_format($pr->total_after_vat,0,'.','') }}</td>
                                        <td style="color:red;text-align:center;">{{ $ro->invoice_no}}</td>
                                        <td style="color:red;border-right:1px solid black;">{{ $pr->createdBy->userDetail->initial }}</td>
                                    </tr>
                                    @php
                                        $totalDPP = $totalDPP-$pr->total_before_vat;
                                        $totDPPperSupplier = $totDPPperSupplier-$pr->total_before_vat;
                                        $totalVAT = $totalVAT-$vat_val;
                                        $totPPNperSupplier = $totPPNperSupplier-$vat_val;
                                        $supplier_name = $supplier->supplier_name;
                                    @endphp
                                @endforeach
                            @endforeach
                            @php
                                $qPR_oth = \App\Models\Tx_purchase_retur::leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('userdetails as usr','tx_purchase_returs.created_by','=','usr.user_id')
                                ->select(
                                    'tx_purchase_returs.total_before_vat',
                                    'tx_purchase_returs.total_after_vat',
                                    'tx_purchase_returs.purchase_retur_date',
                                    'tx_purchase_returs.purchase_retur_no',
                                    'tx_purchase_returs.vat_val',
                                    'tx_ro.po_or_pm_no',
                                    'tx_ro.invoice_no',
                                    'usr.initial as createdUserInit',
                                )
                                ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_purchase_returs.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_purchase_returs.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_purchase_returs.approved_by IS NOT NULL')
                                ->whereRaw('tx_ro.receipt_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->where([
                                    'tx_purchase_returs.supplier_id'=>$supplier->supplier_id,
                                    'tx_purchase_returs.active'=>'Y',
                                    'tx_ro.branch_id'=>$branch->id,
                                ])
                                ->orderBy('tx_purchase_returs.purchase_retur_date','ASC')
                                ->get();
                            @endphp
                            @foreach ($qPR_oth as $qPR_o)
                                @php
                                    $pr_before_vat_ppn = 0;
                                    $vat_val = 0;
                                    $pr_before_vat_ppn = $qPR_o->total_after_vat-$qPR_o->total_before_vat;

                                    $totalDPP = $totalDPP-$qPR_o->total_before_vat;
                                    $totDPPperSupplier = $totDPPperSupplier-$qPR_o->total_before_vat;
                                    $totalVAT = $totalVAT-$pr_before_vat_ppn;
                                    $totPPNperSupplier = $totPPNperSupplier-$pr_before_vat_ppn;                                    
                                @endphp
                                <tr>
                                    {{-- <td style="color:red;border-left:1px solid black;">{{ $supplier_name }}</td> --}}
                                    <td style="color:red;border-left:1px solid black;">{{ ($supplier_name!=$supplier->supplier_name)?$supplier->supplier_name:'' }}</td>
                                    <td style="color:red;text-align:center;">{{ date_format(date_create($qPR_o->purchase_retur_date),"d/m/Y") }}</td>
                                    <td style="color:red;">&nbsp;</td>
                                    <td style="color:red;text-align:center;">{{ $qPR_o->purchase_retur_no}}</td>
                                    <td style="color:red;text-align:center;">{!! str_replace(",","<br/>",substr($qPR_o->po_or_pm_no,1,strlen($qPR_o->po_or_pm_no))) !!}</td>
                                    <td style="color:red;text-align:right;">-{{ number_format($qPR_o->total_before_vat,0,'.','') }}</td>
                                    <td style="color:red;text-align:right;">-{{ number_format($pr_before_vat_ppn,0,'.','') }}</td>
                                    <td style="color:red;text-align:right;">-{{ number_format($qPR_o->total_after_vat,0,'.','') }}</td>
                                    <td style="color:red;text-align:center;">{{ $qPR_o->invoice_no}}</td>
                                    <td style="color:red;border-right:1px solid black;">{{ $qPR_o->createdUserInit }}</td>
                                </tr>
                                @php
                                    $supplier_name = $supplier->supplier_name;
                                @endphp
                            @endforeach
                            @if ($totDPPperSupplier>0)
                                <tr>
                                    <td style="border-left:1px solid black;">&nbsp;</td>
                                    <td style="text-align: center;font-weight:700;">SUB TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="font-weight: 700;text-align:right;">{{ number_format($totDPPperSupplier,0,'.','') }}</td>
                                    <td style="font-weight: 700;text-align:right;">{{ number_format($totPPNperSupplier,0,'.','') }}</td>
                                    <td style="font-weight: 700;text-align:right;">{{ number_format($totDPPperSupplier+$totPPNperSupplier,0,'.','') }}</td>
                                    <td>&nbsp;</td>
                                    <td style="border-right:1px solid black;">&nbsp;</td>
                                </tr>
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
                                    <td style="border-right:1px solid black;">&nbsp;</td>
                                </tr>
                            @else
                                @if ($receipt_orders->count()>0)
                                    <tr>
                                        <td style="border-left:1px solid black;">&nbsp;</td>
                                        <td style="text-align: center;font-weight:700;">SUB TOTAL</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="font-weight: 700;text-align:right;">0</td>
                                        <td style="font-weight: 700;text-align:right;">0</td>
                                        <td style="font-weight: 700;text-align:right;">0</td>
                                        <td>&nbsp;</td>
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>
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
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>                                    
                                @endif
                            @endif
                            @php
                                $totalPerBranchDPP += $totDPPperSupplier;
                                $totalPerBranchVAT += $totPPNperSupplier;
                            @endphp
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td style="text-align: center;font-weight:700;">SUB TOTAL {{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-weight: 700;text-align:right;">{{ number_format($totalPerBranchDPP,0,'.','') }}</td>
                            <td style="font-weight: 700;text-align:right;">{{ number_format($totalPerBranchVAT,0,'.','') }}</td>
                            <td style="font-weight: 700;text-align:right;">{{ number_format($totalPerBranchDPP+$totalPerBranchVAT,0,'.','') }}</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
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
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-bottom:1px solid black;">GRAND TOTAL</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align:right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalDPP,0,'.','') }}</td>
                        <td style="text-align:right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalVAT,0,'.','') }}</td>
                        <td style="text-align:right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalDPP+$totalVAT,0,'.','') }}</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
