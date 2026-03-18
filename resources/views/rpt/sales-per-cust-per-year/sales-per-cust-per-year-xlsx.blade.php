<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesPerCustPerYear</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    if ($period_year<date_format($date,"Y")){
                        $month = 12;
                    }
                    $totCols = 4+$month;
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
                        <th colspan="{{ $totCols }}">SALES PER CUSTOMER PER YEAR</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $period_year }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format(date_add(date_create(date('Y-m-d')),date_interval_create_from_date_string("7 hours")),"d M Y") }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA CUST</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                        @for ($i=1;$i<=$month;$i++)
                            @switch($i)
                                @case(1)
                                    @php
                                        $monthNm = 'JAN';
                                    @endphp
                                    @break
                                @case(2)
                                    @php
                                        $monthNm = 'FEB';
                                    @endphp
                                    @break
                                @case(3)
                                    @php
                                        $monthNm = 'MAR';
                                    @endphp
                                    @break
                                @case(4)
                                    @php
                                        $monthNm = 'APR';
                                    @endphp
                                    @break
                                @case(5)
                                    @php
                                        $monthNm = 'MAY';
                                    @endphp
                                    @break
                                @case(6)
                                    @php
                                        $monthNm = 'JUN';
                                    @endphp
                                    @break
                                @case(7)
                                    @php
                                        $monthNm = 'JUL';
                                    @endphp
                                    @break
                                @case(8)
                                    @php
                                        $monthNm = 'AUG';
                                    @endphp
                                    @break
                                @case(9)
                                    @php
                                        $monthNm = 'SEP';
                                    @endphp
                                    @break
                                @case(10)
                                    @php
                                        $monthNm = 'OCT';
                                    @endphp
                                    @break
                                @case(11)
                                    @php
                                        $monthNm = 'NOP';
                                    @endphp
                                    @break
                                @case(12)
                                    @php
                                        $monthNm = 'DEC';
                                    @endphp
                                    @break
                                @default
                                    @php
                                        $monthNm = '';
                                    @endphp
                            @endswitch
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">{{ $monthNm }}</th>
                        @endfor
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totAllPricePerYear = array(0,0,0,0,0,0,0,0,0,0,0,0);
                        $totAll = 0;
                        $totAllavg = 0;
                        $branch_name='';

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $totAllPricePerMonth = array(0,0,0,0,0,0,0,0,0,0,0,0);
                            $totAllPricePerBranch = 0;
                            $totAllAVGPerBranch = 0;
                        @endphp
                        <tr>
                            <td style="font-weight: 700;">{{ ($branch_name!=$branch->name)?$branch->name:'' }}</td>
                            @if ($branch_name!=$branch->name)
                                @php
                                    $branch_name = $branch->name;
                                @endphp
                            @endif
                            <td>&nbsp;</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td>&nbsp;</td>
                            @endfor
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @php
                            $qCust = \App\Models\Mst_customer::leftJoin('userdetails','mst_customers.salesman_id','=','userdetails.user_id')
                            ->leftJoin('users','userdetails.user_id','=','users.id')
                            ->select(
                                'mst_customers.id as cust_id',
                                'mst_customers.name as cust_name',
                                'mst_customers.customer_unique_code',
                                'userdetails.initial as salesman_initial',
                            )
                            ->where([
                                'mst_customers.active'=>'Y',
                                'userdetails.is_salesman'=>'Y',
                                'userdetails.branch_id'=>$branch->id,
                            ])
                            ->where(function($q) use($period_year){
                                $q->whereIn('mst_customers.id', function($q) use($period_year) {
                                    $q->select('customer_id')
                                    ->from('tx_sales_orders')
                                    ->whereRaw('sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('DATE_FORMAT(sales_order_date, "%Y")=\''.$period_year.'\'')
                                    ->where([
                                        'active'=>'Y',
                                    ]);
                                })
                                ->orWhereIn('mst_customers.id', function($q) use($period_year) {
                                    $q->select('customer_id')
                                    ->from('tx_surat_jalans')
                                    ->whereRaw('surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('DATE_FORMAT(surat_jalan_date, "%Y")=\''.$period_year.'\'')
                                    ->where([
                                        'active'=>'Y',
                                    ]);
                                });
                            })
                            ->orderBy('userdetails.initial','ASC')
                            ->orderBy('mst_customers.name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qCust as $qC)
                            @php
                                $totAllPrice = 0;
                            @endphp
                            <tr>
                                <td>{{ $qC->customer_unique_code.' - '.$qC->cust_name }}</td>
                                <td style="text-align: center;">{{ $qC->salesman_initial }}</td>
                                @for ($i=1;$i<=$month;$i++)
                                    @php
                                        // total DPP
                                        $totPriceSO = 0;
                                        $totPriceNR = 0;
                                        if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || $lokal_input=='x'){
                                            // SO - begin
                                            $qSoP = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders as tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_so.customer_id','=','mst_cust.id')
                                            ->whereIn('tx_sales_order_parts.order_id', function($q) {
                                                $q->select('tx_dop.sales_order_id')
                                                ->from('tx_delivery_order_parts as tx_dop')
                                                ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                                ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                                ->where([
                                                    'tx_dop.active'=>'Y',
                                                    'tx_do.active'=>'Y',
                                                ]);

                                            })
                                            ->selectRaw('SUM(tx_sales_order_parts.qty*tx_sales_order_parts.price) as tot_price')
                                            ->selectRaw('SUM(tx_sales_order_parts.qty*tx_sales_order_parts.price*tx_so.vat_val/100) as tot_vat')
                                            ->whereRaw('DATE_FORMAT(tx_so.sales_order_date, "%Y-%m")=\''.$period_year.'-'.(strlen($i)==1?'0'.$i:$i).'\'')
                                            ->where([
                                                'tx_sales_order_parts.active'=>'Y',
                                                'tx_so.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active'=>'Y',
                                            ])
                                            ->first();
                                            if($qSoP){
                                                // $totPriceSO = $qSoP->tot_price;
                                                $totPriceSO = $qSoP->tot_price+$qSoP->tot_vat;
                                            }
                                            // SO - end

                                            // NR - begin
                                            $qNrP = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_nr.customer_id','=','mst_cust.id')
                                            ->selectRaw("SUM(tx_nota_retur_parts.qty_retur*tx_nota_retur_parts.final_price) as tot_price")
                                            ->selectRaw("SUM(tx_nota_retur_parts.qty_retur*tx_nota_retur_parts.final_price*tx_nr.vat_val/100) as tot_vat")
                                            ->whereRaw('DATE_FORMAT(tx_nr.nota_retur_date, "%Y-%m")=\''.$period_year.'-'.(strlen($i)==1?'0'.$i:$i).'\'')
                                            ->where([
                                                'tx_nota_retur_parts.active' => 'Y',
                                                'tx_nr.active' => 'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->first();
                                            if($qNrP){
                                                $totPriceNR = $qNrP->tot_price+$qNrP->tot_vat;
                                            }
                                            // NR - end
                                        }

                                        $totPriceSJ = 0;
                                        $totPriceRE = 0;
                                        if ($lokal_input=='A' || $lokal_input=='N'){
                                            // SJ - begin
                                            $qPriceSJ = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans as tx_sj','tx_surat_jalan_parts.surat_jalan_id','=','tx_sj.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_sj.customer_id','=','mst_cust.id')
                                            ->selectRaw("SUM(tx_surat_jalan_parts.qty*tx_surat_jalan_parts.price) as tot_price")
                                            ->whereIn('tx_surat_jalan_parts.surat_jalan_id', function($q) {
                                                $q->select('tx_dop.sales_order_id')
                                                ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                                ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                                ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                                ->where([
                                                    'tx_dop.active'=>'Y',
                                                    'tx_do.active'=>'Y',
                                                ]);

                                            })
                                            ->whereRaw('DATE_FORMAT(tx_sj.surat_jalan_date, "%Y-%m")=\''.$period_year.'-'.(strlen($i)==1?'0'.$i:$i).'\'')
                                            ->where([
                                                'tx_surat_jalan_parts.active' => 'Y',
                                                'tx_sj.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->first();
                                            if ($qPriceSJ){
                                                $totPriceSJ = $qPriceSJ->tot_price;
                                            }
                                            // SJ - end

                                            // RE - begin
                                            $qPriceRE = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_re','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_re.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_re.customer_id','=','mst_cust.id')
                                            ->selectRaw("SUM(tx_nota_retur_part_non_taxes.qty_retur*tx_nota_retur_part_non_taxes.final_price) as tot_price")
                                            ->whereRaw('DATE_FORMAT(tx_re.nota_retur_date, "%Y-%m")=\''.$period_year.'-'.(strlen($i)==1?'0'.$i:$i).'\'')
                                            ->where([
                                                'tx_nota_retur_part_non_taxes.active' => 'Y',
                                                'tx_re.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->first();
                                            if($qPriceRE){
                                                $totPriceRE = $qPriceRE->tot_price;
                                            }
                                            // RE - end
                                        }

                                    @endphp
                                    <td style="text-align: right;">{{ number_format(($totPriceSO-$totPriceNR)+($totPriceSJ-$totPriceRE),0,'.','') }}</td>
                                    @php
                                        $totAllPrice += ($totPriceSO-$totPriceNR)+($totPriceSJ-$totPriceRE);
                                        $totAllPricePerMonth[$i-1] += ($totPriceSO-$totPriceNR)+($totPriceSJ-$totPriceRE);
                                    @endphp
                                @endfor
                                @php
                                    $totAllPricePerBranch += $totAllPrice;
                                @endphp
                                <td style="text-align: right;">{{ number_format($totAllPrice,0,'.','') }}</td>
                                <td style="text-align: right;">
                                    @php
                                        $totAVG_DO = 0;

                                        if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || $lokal_input=='x'){
                                            // faktur
                                            $qFK = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
                                            ->leftJoin('tx_sales_orders as tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_delivery_order_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_so.customer_id','=','mst_cust.id')
                                            ->select(
                                                'tx_delivery_order_parts.part_id as partid',
                                                'tx_delivery_order_parts.qty as qty',
                                                'tx_delivery_order_parts.updated_at as updatedat',
                                                'tx_sop.last_avg_cost',
                                            )
                                            ->whereRaw('DATE_FORMAT(tx_so.sales_order_date, "%Y")=\''.$period_year.'\'')
                                            ->where([
                                                'tx_delivery_order_parts.active'=>'Y',
                                                'tx_do.active'=>'Y',
                                                'tx_so.active'=>'Y',
                                                'tx_sop.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active'=>'Y',
                                            ])
                                            ->get();
                                            foreach ($qFK as $do) {
                                                $totAVG_DO += ($do->last_avg_cost*$do->qty);
                                            }

                                            // nota retur
                                            $qNR = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_nr.customer_id','=','mst_cust.id')
                                            ->select(
                                                'tx_nota_retur_parts.part_id as partid',
                                                'tx_nota_retur_parts.qty_retur',
                                                'tx_nota_retur_parts.updated_at as updatedat',
                                                'tx_sop.last_avg_cost',
                                            )
                                            ->whereRaw('DATE_FORMAT(tx_nr.nota_retur_date, "%Y")=\''.$period_year.'\'')
                                            ->where([
                                                'tx_nota_retur_parts.active' => 'Y',
                                                'tx_nr.active' => 'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->get();
                                            foreach ($qNR as $nr) {
                                                $totAVG_DO = $totAVG_DO-($nr->last_avg_cost*$nr->qty_retur);
                                            }
                                        }

                                        if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N'){
                                            // nota penjualan
                                            $qNP = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes as tx_do','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_do.id')
                                            ->leftJoin('tx_surat_jalans as tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_delivery_order_non_tax_parts.sales_order_part_id','=','tx_sjp.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_sj.customer_id','=','mst_cust.id')
                                            ->select(
                                                'tx_delivery_order_non_tax_parts.part_id as partid',
                                                'tx_delivery_order_non_tax_parts.qty as qty',
                                                'tx_delivery_order_non_tax_parts.updated_at as updatedat',
                                                'tx_sjp.last_avg_cost',
                                            )
                                            ->whereRaw('DATE_FORMAT(tx_sj.surat_jalan_date, "%Y")=\''.$period_year.'\'')
                                            ->where([
                                                'tx_delivery_order_non_tax_parts.active' => 'Y',
                                                'tx_sj.active'=>'Y',
                                                'tx_sjp.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->get();
                                            foreach ($qNP as $np) {
                                                $totAVG_DO += ($np->last_avg_cost*$np->qty);
                                            }

                                            // retur
                                            $qRE = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_re','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_re.id')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                            ->leftJoin('mst_customers as mst_cust','tx_re.customer_id','=','mst_cust.id')
                                            ->select(
                                                'tx_nota_retur_part_non_taxes.part_id as partid',
                                                'tx_nota_retur_part_non_taxes.qty_retur',
                                                'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                                'tx_sjp.last_avg_cost',
                                            )
                                            ->whereRaw('DATE_FORMAT(tx_re.nota_retur_date, "%Y")=\''.$period_year.'\'')
                                            ->where([
                                                'tx_nota_retur_part_non_taxes.active' => 'Y',
                                                'tx_re.active'=>'Y',
                                                'mst_cust.id'=>$qC->cust_id,
                                                'mst_cust.active' => 'Y',
                                            ])
                                            ->get();
                                            foreach ($qRE as $re) {
                                                $totAVG_DO = $totAVG_DO-($re->last_avg_cost*$re->qty_retur);
                                            }
                                        }

                                        $totAllAVGPerBranch += $totAVG_DO;
                                    @endphp
                                    {{ ($totAllPrice>0)?number_format((($totAllPrice-$totAVG_DO)/$totAllPrice)*100,0,'.',''):0 }}%
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: right;font-weight: 700;">{{ number_format($totAllPricePerMonth[$i-1],0,'.','') }}</td>
                                @php
                                    $totAllPricePerYear[$i-1] += $totAllPricePerMonth[$i-1];
                                @endphp
                            @endfor
                            <td style="text-align: right;font-weight: 700;">{{ number_format($totAllPricePerBranch,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;">{{ ($totAllPricePerBranch>0)?number_format((($totAllPricePerBranch-$totAllAVGPerBranch)/$totAllPricePerBranch)*100,0,'.',''):0 }}%</td>
                        </tr>
                        @php
                            $totAllavg += $totAllAVGPerBranch;
                        @endphp
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight: 700;">TOTAL</td>
                        <td>&nbsp;</td>
                        @for ($i=1;$i<=$month;$i++)
                            <td style="text-align: right;font-weight: 700;">{{ number_format($totAllPricePerYear[$i-1],0,'.','') }}</td>
                            @php
                                $totAll += $totAllPricePerYear[$i-1];
                            @endphp
                        @endfor
                        <td style="text-align: right;font-weight: 700;">{{ number_format($totAll,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;">{{ ($totAll>0)?number_format((($totAll-$totAllavg)/$totAll)*100,0,'.',''):0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
