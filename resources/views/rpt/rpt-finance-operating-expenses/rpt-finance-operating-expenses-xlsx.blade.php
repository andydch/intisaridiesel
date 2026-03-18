<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>OperatingExpenses</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    switch (strtolower($lokal_input)) {
                        case 'p':
                            //empty
                            break;
                        case 'n':
                            //empty
                            break;
                        default:
                            $lokal_input = 'a';
                    }

                    $months = explode(',','JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOP,DEC');
                    $columns = 4;
                    if ($month_id==0){$columns = 15;}
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $columns }}">{!! ($qCompany?$qCompany->name:'') !!}</th>
                    </tr>
                    <tr>
                        @php
                            $branches = \App\Models\Mst_branch::where([
                                'id' => $branch_id,
                            ])
                            ->first();
                        @endphp
                        <th colspan="{{ $columns }}">Branch: {!! ($branches?$branches->name:'All') !!}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $columns }}">OPERATING EXPENSES REPORT</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $columns }}">PERIOD:&nbsp;{{ $year_id }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: left;">{{ strtoupper($lokal_input) }}</th>
                        <th colspan="{{ $columns-2 }}">&nbsp;</th>
                        <th style="text-align: right;">{{ date_format(now(),"d/m/Y") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid black;background-color: #ff6600;">COA NO</td>
                        <td style="border: 1px solid black;background-color: #ff6600;">COA NAME</td>
                        @if ($month_id==0)
                            @foreach ($months as $month)
                                <td style="border: 1px solid black;background-color: #ff6600;">{{ $month }}</td>
                            @endforeach
                        @else
                            <td style="border: 1px solid black;background-color: #ff6600;">{{ $months[$month_id-1] }}</td>
                        @endif
                        <td style="border: 1px solid black;background-color: #ff6600;">TOTAL</td>
                    </tr>
                    @php
                        $qCoas = \App\Models\Mst_coa::whereIn('id', function ($q) use($year_id) {
                            $q->select('tx_gjd.coa_id')
                            ->from('tx_general_journal_details as tx_gjd')
                            ->leftJoin('tx_general_journals as tx_gj','tx_gjd.general_journal_id','=','tx_gj.id')
                            ->where([
                                'tx_gjd.active'=>'Y',
                                'tx_gj.active'=>'Y',
                            ])
                            ->whereRaw('DATE_FORMAT(tx_gj.general_journal_date, "%Y")=\''.$year_id.'\'');
                        })
                        ->orWhereIn('id', function ($q) use($year_id) {
                            $q->select('tx_ljd.coa_id')
                            ->from('tx_lokal_journal_details as tx_ljd')
                            ->leftJoin('tx_lokal_journals as tx_lj','tx_ljd.lokal_journal_id','=','tx_lj.id')
                            ->where([
                                'tx_ljd.active'=>'Y',
                                'tx_lj.active'=>'Y',
                            ])
                            ->whereRaw('DATE_FORMAT(tx_lj.general_journal_date, "%Y")=\''.$year_id.'\'');
                        })
                        ->orderBy('coa_name','ASC')
                        ->get();

                        $grandTotal = 0;
                        $totalPerMonth = [0,0,0,0,0,0,0,0,0,0,0,0];
                        $totalOneMonth = 0;
                    @endphp
                    @foreach ($qCoas as $coa)
                        @php
                            $totalPerCoa = 0;
                        @endphp
                        <tr>
                            <td style="border-left: 1px solid black;">{{ $coa->coa_code_complete }}</td>
                            <td>{{ $coa->coa_name }}</td>
                            @if ($month_id==0)
                                @php
                                    $iMonth = 0;
                                @endphp
                                @foreach ($months as $month)
                                    @php
                                        $totalDebit = 0;
                                        $totalKredit = 0;
                                        $qG = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                                        ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                                        ->select(
                                            'tx_general_journal_details.debit',
                                            'tx_general_journal_details.kredit',
                                            'tx_gj.module_no',
                                            'usr_d.branch_id as branch_id',
                                        )
                                        ->whereRaw('DATE_FORMAT(tx_gj.general_journal_date, "%Y-%m")=\''.$year_id.'-'.(strlen(($iMonth+1))==1?'0'.($iMonth+1):($iMonth+1)).'\'')
                                        ->where([
                                            'tx_general_journal_details.coa_id'=>$coa->id,
                                        ]);

                                        $qL = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                                        ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                                        ->select(
                                            'tx_lokal_journal_details.debit',
                                            'tx_lokal_journal_details.kredit',
                                            'tx_lj.module_no',
                                            'usr_d.branch_id as branch_id',
                                        )
                                        ->whereRaw('DATE_FORMAT(tx_lj.general_journal_date, "%Y-%m")=\''.$year_id.'-'.(strlen(($iMonth+1))==1?'0'.($iMonth+1):($iMonth+1)).'\'')
                                        ->where([
                                            'tx_lokal_journal_details.coa_id'=>$coa->id,
                                        ]);

                                        $allJd = [];
                                        if (strtolower($lokal_input)=='a' || strtolower($lokal_input)=='x'){
                                            $allJd = $qL->union($qG)
                                            ->get();
                                        }
                                        if (strtolower($lokal_input)=='p'){
                                            $allJd = $qG->get();
                                        }
                                        if (strtolower($lokal_input)=='n'){
                                            $allJd = $qL->get();
                                        }
                                    @endphp
                                    @foreach ($allJd as $journal)
                                        @if ($branch_id==0)
                                            {{-- all branches --}}
                                            @php
                                                $totalDebit += $journal->debit;
                                                $totalKredit += $journal->kredit;
                                            @endphp
                                        @else
                                            @php
                                                $branch_id_tmp = 0;
                                            @endphp
                                            @if (strpos("J-".$journal->module_no,env('P_FAKTUR'))>0)
                                                {{-- faktur --}}
                                                @php
                                                    $qFaktur = \App\Models\Tx_delivery_order::where([
                                                        'delivery_order_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qFaktur){$branch_id_tmp = $qFaktur->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_NOTA_RETUR'))>0)
                                                {{-- nota retur --}}
                                                @php
                                                    $qNotaRetur = \App\Models\Tx_nota_retur::where([
                                                        'nota_retur_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qNotaRetur){$branch_id_tmp = $qNotaRetur->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_NOTA_PENJUALAN'))>0)
                                                {{-- nota penjualan --}}
                                                @php
                                                    $qNotaPenjualan = \App\Models\Tx_delivery_order_non_tax::where([
                                                        'delivery_order_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qNotaPenjualan){$branch_id_tmp = $qNotaPenjualan->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_RETUR'))>0)
                                                {{-- retur --}}
                                                @php
                                                    $qRetur = \App\Models\Tx_nota_retur_non_tax::where([
                                                        'nota_retur_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qRetur){$branch_id_tmp = $qRetur->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_RECEIPT_ORDER'))>0)
                                                {{-- receipt order --}}
                                                @php
                                                    $qRO = \App\Models\Tx_receipt_order::where([
                                                        'receipt_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qRO){$branch_id_tmp = $qRO->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_PAYMENT_RECEIPT'))>0)
                                                {{-- payment receipt / pembayaran customer --}}
                                                @php
                                                    $qPembCust = \App\Models\Tx_payment_receipt::leftJoin('userdetails as usr_d','tx_payment_receipts.created_by','=','usr_d.user_id')
                                                    ->select(
                                                        'usr_d.branch_id',
                                                    )
                                                    ->where([
                                                        'tx_payment_receipts.payment_receipt_no'=>$journal->module_no,
                                                        'usr_d.branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qPembCust){$branch_id_tmp = $qPembCust->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_PAYMENT_VOUCHER'))>0)
                                                {{-- payment voucher / pembayaran supplier --}}
                                                @php
                                                    $qPembSupp = \App\Models\Tx_payment_voucher::leftJoin('userdetails as usr_d','tx_payment_vouchers.created_by','=','usr_d.user_id')
                                                    ->select(
                                                        'usr_d.branch_id',
                                                    )
                                                    ->where([
                                                        'tx_payment_vouchers.payment_voucher_no'=>$journal->module_no,
                                                        'usr_d.branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qPembSupp){$branch_id_tmp = $qPembSupp->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_STOCK_ADJUSTMENT'))>0)
                                                {{-- stock adjusment --}}
                                                @php
                                                    $qStockAdj = \App\Models\Tx_stock_adjustment::where([
                                                        'stock_adj_no'=>$journal->module_no,
                                                        'branch_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qStockAdj){$branch_id_tmp = $qStockAdj->branch_id;}
                                                @endphp
                                            @endif
                                            @if (strpos("J-".$journal->module_no,env('P_STOCK_TRANSFER'))>0)
                                                {{-- stock transfer --}}
                                                @php
                                                    $qStockTrf = \App\Models\Tx_stock_transfer::where([
                                                        'stock_transfer_no'=>$journal->module_no,
                                                        'branch_to_id'=>$branch_id,
                                                    ])
                                                    ->first();
                                                    if ($qStockTrf){$branch_id_tmp = $qStockTrf->branch_to_id;}
                                                @endphp
                                            @endif
                                            @if ($branch_id_tmp==0)
                                                @php
                                                    $branch_id_tmp = $journal->branch_id;
                                                @endphp
                                            @endif
                                            @if ($branch_id_tmp==$branch_id)
                                                @php
                                                    $totalDebit += $journal->debit;
                                                    $totalKredit += $journal->kredit;
                                                @endphp
                                            @endif

                                        @endif
                                    @endforeach
                                    <td>{{ $totalDebit-$totalKredit }}</td>
                                    @php
                                        $totalPerCoa += ($totalDebit-$totalKredit);
                                        $totalPerMonth[$iMonth] += ($totalDebit-$totalKredit);

                                        $iMonth += 1;
                                    @endphp
                                @endforeach

                            @else
                                @php
                                    $totalDebit = 0;
                                    $totalKredit = 0;
                                    $qG = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                                    ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                                    ->select(
                                        'tx_general_journal_details.debit',
                                        'tx_general_journal_details.kredit',
                                        'tx_gj.module_no',
                                        'usr_d.branch_id as branch_id',
                                    )
                                    ->whereRaw('DATE_FORMAT(tx_gj.general_journal_date, "%Y-%m")=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where([
                                        'tx_general_journal_details.coa_id'=>$coa->id,
                                    ]);

                                    $qL = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                                    ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                                    ->select(
                                        'tx_lokal_journal_details.debit',
                                        'tx_lokal_journal_details.kredit',
                                        'tx_lj.module_no',
                                        'usr_d.branch_id as branch_id',
                                    )
                                    ->whereRaw('DATE_FORMAT(tx_lj.general_journal_date, "%Y-%m")=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where([
                                        'tx_lokal_journal_details.coa_id'=>$coa->id,
                                    ]);

                                    $allJd = [];
                                    if (strtolower($lokal_input)=='a' || strtolower($lokal_input)=='x'){
                                        $allJd = $qL->union($qG)
                                        ->get();
                                    }
                                    if (strtolower($lokal_input)=='p'){
                                        $allJd = $qG->get();
                                    }
                                    if (strtolower($lokal_input)=='n'){
                                        $allJd = $qL->get();
                                    }
                                @endphp
                                @foreach ($allJd as $journal)
                                    @if ($branch_id==0)
                                        {{-- all branches --}}
                                        @php
                                            $totalDebit += $journal->debit;
                                            $totalKredit += $journal->kredit;
                                        @endphp
                                    @else
                                        @php
                                            $branch_id_tmp = 0;
                                        @endphp
                                        @if (strpos("J-".$journal->module_no,env('P_FAKTUR'))>0)
                                            {{-- faktur --}}
                                            @php
                                                $qFaktur = \App\Models\Tx_delivery_order::where([
                                                    'delivery_order_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qFaktur){$branch_id_tmp = $qFaktur->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_NOTA_RETUR'))>0)
                                            {{-- nota retur --}}
                                            @php
                                                $qNotaRetur = \App\Models\Tx_nota_retur::where([
                                                    'nota_retur_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qNotaRetur){$branch_id_tmp = $qNotaRetur->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_NOTA_PENJUALAN'))>0)
                                            {{-- nota penjualan --}}
                                            @php
                                                $qNotaPenjualan = \App\Models\Tx_delivery_order_non_tax::where([
                                                    'delivery_order_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qNotaPenjualan){$branch_id_tmp = $qNotaPenjualan->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_RETUR'))>0)
                                            {{-- retur --}}
                                            @php
                                                $qRetur = \App\Models\Tx_nota_retur_non_tax::where([
                                                    'nota_retur_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qRetur){$branch_id_tmp = $qRetur->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_RECEIPT_ORDER'))>0)
                                            {{-- receipt order --}}
                                            @php
                                                $qRO = \App\Models\Tx_receipt_order::where([
                                                    'receipt_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qRO){$branch_id_tmp = $qRO->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_PAYMENT_RECEIPT'))>0)
                                            {{-- payment receipt / pembayaran customer --}}
                                            @php
                                                $qPembCust = \App\Models\Tx_payment_receipt::leftJoin('userdetails as usr_d','tx_payment_receipts.created_by','=','usr_d.user_id')
                                                ->select(
                                                    'usr_d.branch_id',
                                                )
                                                ->where([
                                                    'tx_payment_receipts.payment_receipt_no'=>$journal->module_no,
                                                    'usr_d.branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qPembCust){$branch_id_tmp = $qPembCust->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_PAYMENT_VOUCHER'))>0)
                                            {{-- payment voucher / pembayaran supplier --}}
                                            @php
                                                $qPembSupp = \App\Models\Tx_payment_voucher::leftJoin('userdetails as usr_d','tx_payment_vouchers.created_by','=','usr_d.user_id')
                                                ->select(
                                                    'usr_d.branch_id',
                                                )
                                                ->where([
                                                    'tx_payment_vouchers.payment_voucher_no'=>$journal->module_no,
                                                    'usr_d.branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qPembSupp){$branch_id_tmp = $qPembSupp->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_STOCK_ADJUSTMENT'))>0)
                                            {{-- stock adjusment --}}
                                            @php
                                                $qStockAdj = \App\Models\Tx_stock_adjustment::where([
                                                    'stock_adj_no'=>$journal->module_no,
                                                    'branch_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qStockAdj){$branch_id_tmp = $qStockAdj->branch_id;}
                                            @endphp
                                        @endif
                                        @if (strpos("J-".$journal->module_no,env('P_STOCK_TRANSFER'))>0)
                                            {{-- stock transfer --}}
                                            @php
                                                $qStockTrf = \App\Models\Tx_stock_transfer::where([
                                                    'stock_transfer_no'=>$journal->module_no,
                                                    'branch_to_id'=>$branch_id,
                                                ])
                                                ->first();
                                                if ($qStockTrf){$branch_id_tmp = $qStockTrf->branch_to_id;}
                                            @endphp
                                        @endif
                                        @if ($branch_id_tmp==0)
                                            @php
                                                $branch_id_tmp = $journal->branch_id;
                                            @endphp
                                        @endif
                                        @if ($branch_id_tmp==$branch_id)
                                            @php
                                                $totalDebit += $journal->debit;
                                                $totalKredit += $journal->kredit;
                                            @endphp
                                        @endif

                                    @endif
                                @endforeach
                                <td>{{ $totalDebit-$totalKredit }}</td>
                                @php
                                    $totalPerCoa += ($totalDebit-$totalKredit);
                                    $totalOneMonth += $totalPerCoa;
                                @endphp
                            @endif
                            <td  style="border-left: 1px solid black;border-right: 1px solid black;">{{ $totalPerCoa }}</td>
                        </tr>
                        @php
                            $grandTotal += $totalPerCoa;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="2"  style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;text-align:center;font-weight:700;">TOTAL</td>
                        @if ($month_id==0)
                            @php
                                $iMonth = 0;
                            @endphp
                            @foreach ($months as $month)
                                <td style="border-top: 1px solid black;border-bottom: 1px solid black;font-weight:700;">{{ $totalPerMonth[$iMonth] }}</td>
                                @php
                                    $iMonth += 1;
                                @endphp
                            @endforeach
                        @else
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black;font-weight:700;">{{ $totalOneMonth }}</td>
                        @endif
                        <td style="border: 1px solid black;font-weight:700;">{{ $grandTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
