<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>FinTxPerAccount</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                <thead>
                    <tr>
                        <th colspan="7">
                            {!! ($qCompany?$qCompany->name:'') !!}
                        </th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="7">TRANSACTION PER ACCOUNT</th>
                    </tr>
                    <tr>
                        <th colspan="7" style="text-align: center;">PERIOD:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalDebit = 0;
                        $totalKredit = 0;
                        $totalAll = 0;

                        $branches = \App\Models\Mst_branch::where([
                            'active'=>'Y',
                        ])
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $qCoa = \App\Models\Mst_coa::where([
                                'id'=>$coa_id
                            ])
                            ->first();

                            $genJd = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                            ->leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                            ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                            ->select(
                                'tx_gj.general_journal_no as general_journal_no',
                                'tx_gj.general_journal_date as general_journal_date',
                                'tx_gj.module_no',
                                'tx_gj.id as id_1',
                                'tx_general_journal_details.id as id_2',
                                'tx_general_journal_details.description as description',
                                'tx_general_journal_details.debit as debit',
                                'tx_general_journal_details.kredit as kredit',
                                'm_coa.coa_code_complete',
                                'm_coa.coa_name',
                                'usr_d.branch_id as branch_id',
                            )
                            ->where([
                                'tx_general_journal_details.coa_id'=>$coa_id,
                                'tx_general_journal_details.active'=>'Y',
                                'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.active'=>'Y',
                            ])
                            ->whereRaw('tx_gj.general_journal_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_gj.general_journal_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->orderBy('tx_gj.general_journal_date','DESC');

                            $lokJd = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                            ->leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                            ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                            ->select(
                                'tx_lj.general_journal_no as general_journal_no',
                                'tx_lj.general_journal_date as general_journal_date',
                                'tx_lj.module_no',
                                'tx_lj.id as id_1',
                                'tx_lokal_journal_details.id as id_2',
                                'tx_lokal_journal_details.description as description',
                                'tx_lokal_journal_details.debit as debit',
                                'tx_lokal_journal_details.kredit as kredit',
                                'm_coa.coa_code_complete',
                                'm_coa.coa_name',
                                'usr_d.branch_id as branch_id',
                            )
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$coa_id,
                                'tx_lokal_journal_details.active'=>'Y',
                                'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.active'=>'Y',
                            ])
                            ->whereRaw('tx_lj.general_journal_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_lj.general_journal_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->orderBy('tx_lj.general_journal_date','DESC')
                            ->union($genJd)
                            ->get();

                            $firstRow = 1;
                        @endphp
                        @if ($lokJd)
                            @foreach ($lokJd as $journal)
                                @php
                                    $branch_id = 0;
                                @endphp
                                @if (strpos("J-".$journal->module_no,env('P_FAKTUR'))>0)
                                    {{-- faktur --}}
                                    @php
                                        $qFaktur = \App\Models\Tx_delivery_order::where([
                                            'delivery_order_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qFaktur){$branch_id = $qFaktur->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_NOTA_RETUR'))>0)
                                    {{-- nota retur --}}
                                    @php
                                        $qNotaRetur = \App\Models\Tx_nota_retur::where([
                                            'nota_retur_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qNotaRetur){$branch_id = $qNotaRetur->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_NOTA_PENJUALAN'))>0)
                                    {{-- nota penjualan --}}
                                    @php
                                        $qNotaPenjualan = \App\Models\Tx_delivery_order_non_tax::where([
                                            'delivery_order_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qNotaPenjualan){$branch_id = $qNotaPenjualan->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_RETUR'))>0)
                                    {{-- retur --}}
                                    @php
                                        $qRetur = \App\Models\Tx_nota_retur_non_tax::where([
                                            'nota_retur_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qRetur){$branch_id = $qRetur->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_RECEIPT_ORDER'))>0)
                                    {{-- receipt order --}}
                                    @php
                                        $qRO = \App\Models\Tx_receipt_order::where([
                                            'receipt_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qRO){$branch_id = $qRO->branch_id;}
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
                                            'usr_d.branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qPembCust){$branch_id = $qPembCust->branch_id;}
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
                                            'usr_d.branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qPembSupp){$branch_id = $qPembSupp->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_STOCK_ADJUSTMENT'))>0)
                                    {{-- stock adjusment --}}
                                    @php
                                        $qStockAdj = \App\Models\Tx_stock_adjustment::where([
                                            'stock_adj_no'=>$journal->module_no,
                                            'branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qStockAdj){$branch_id = $qStockAdj->branch_id;}
                                    @endphp
                                @endif
                                @if (strpos("J-".$journal->module_no,env('P_STOCK_TRANSFER'))>0)
                                    {{-- stock transfer --}}
                                    @php
                                        $qStockTrf = \App\Models\Tx_stock_transfer::where([
                                            'stock_transfer_no'=>$journal->module_no,
                                            'branch_to_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qStockTrf){$branch_id = $qStockTrf->branch_to_id;}
                                    @endphp
                                @endif
                                @if ($branch_id==0)
                                    @php
                                        $branch_id = $journal->branch_id;
                                    @endphp
                                @endif
                                @if ($branch_id==$branch->id)
                                    @if ($firstRow==1)
                                        <tr>
                                            <td style="text-align: center;">{{ ($qCoa?$qCoa->coa_code_complete:'') }}</td>
                                            <td>{{ ($qCoa?$qCoa->coa_name:'') }}</td>
                                            <td>{{ $branch->name }}</td>
                                            <td colspan="3">&nbsp;</td>
                                            <td style="text-align: center;">{{ date("d-M-Y") }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DATE</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DOCUMENT NO</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DESCRIPTION</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">PPN</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">NON</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">TOTAL</td>
                                            <td style="text-align: center;border:1px solid black;background-color:#ff6600;">PAYMENT</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($journal->general_journal_date),"d-M-Y") }}</td>
                                        <td style="text-align: center;border:1px solid black;">{{ $journal->general_journal_no }}</td>
                                        <td style="text-align: left;">{{ $journal->description }}</td>
                                        <td style="text-align: right;border:1px solid black;">{{ $journal->debit }}</td>
                                        <td style="text-align: right;border:1px solid black;">{{ $journal->kredit }}</td>
                                        <td style="text-align: right;">{{ ($journal->debit>0?$journal->debit:$journal->kredit) }}</td>
                                        @php
                                            $totalDebit += $journal->debit;
                                            $totalKredit += $journal->kredit;
                                            $totalAll += ($journal->debit>0?$journal->debit:$journal->kredit);

                                            $qJdtl = [];
                                        @endphp
                                        @if (strpos("J-".$journal->general_journal_no,env('P_GENERAL_JURNAL'))>0)
                                            {{-- general journal --}}
                                            @if ($journal->debit>0)
                                                @php
                                                    $qJdtl = \App\Models\Tx_general_journal_detail::leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                                                    ->select(
                                                        'm_coa.coa_code_complete',
                                                        'm_coa.coa_name',
                                                    )
                                                    ->whereRaw('tx_general_journal_details.id<>'.$journal->id_2)
                                                    ->where([
                                                        'tx_general_journal_details.general_journal_id'=>$journal->id_1,
                                                        'tx_general_journal_details.kredit'=>$journal->debit,
                                                    ])
                                                    ->first();
                                                @endphp
                                            @endif
                                            @if ($journal->kredit>0)
                                                @php
                                                    $qJdtl = \App\Models\Tx_general_journal_detail::leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                                                    ->select(
                                                        'm_coa.coa_code_complete',
                                                        'm_coa.coa_name',
                                                    )
                                                    ->whereRaw('tx_general_journal_details.id<>'.$journal->id_2)
                                                    ->where([
                                                        'tx_general_journal_details.general_journal_id'=>$journal->id_1,
                                                        'tx_general_journal_details.kredit'=>$journal->debit,
                                                    ])
                                                    ->first();
                                                @endphp
                                            @endif
                                        @endif
                                        @if (strpos("J-".$journal->general_journal_no,env('P_LOKAL_JURNAL'))>0)
                                            {{-- lokal journal --}}
                                            @if ($journal->debit>0)
                                                @php
                                                    $qJdtl = \App\Models\Tx_lokal_journal_detail::leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                                                    ->select(
                                                        'm_coa.coa_code_complete',
                                                        'm_coa.coa_name',
                                                    )
                                                    ->whereRaw('tx_lokal_journal_details.id<>'.$journal->id_2)
                                                    ->where([
                                                        'tx_lokal_journal_details.lokal_journal_id'=>$journal->id_1,
                                                        'tx_lokal_journal_details.kredit'=>$journal->debit,
                                                    ])
                                                    ->first();
                                                @endphp
                                            @endif
                                            @if ($journal->kredit>0)
                                                @php
                                                    $qJdtl = \App\Models\Tx_lokal_journal_detail::leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                                                    ->select(
                                                        'm_coa.coa_code_complete',
                                                        'm_coa.coa_name',
                                                    )
                                                    ->whereRaw('tx_lokal_journal_details.id<>'.$journal->id_2)
                                                    ->where([
                                                        'tx_lokal_journal_details.lokal_journal_id'=>$journal->id_1,
                                                        'tx_lokal_journal_details.kredit'=>$journal->debit,
                                                    ])
                                                    ->first();
                                                @endphp
                                            @endif
                                        @endif
                                        <td style="text-align: center;border:1px solid black;">{{ ($qJdtl?$qJdtl->coa_name:'-') }}</td>
                                    </tr>
                                @endif
                                @php
                                    $firstRow += 1;
                                @endphp
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ $totalDebit }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ $totalKredit }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ $totalAll }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
