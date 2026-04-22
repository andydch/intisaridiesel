<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags x -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>FinTxJournal</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                <thead>
                    <tr>
                        <th colspan="6">
                            {!! ($qCompany?$qCompany->name:'') !!}
                        </th>
                    </tr>
                    <tr>
                        <th colspan="6">
                            @if ($branch_id=='' || $branch_id=='0')
                                {{ '' }}
                            @else
                                @php
                                    $branch_row = \App\Models\Mst_branch::where([
                                        'id'=>$branch_id
                                    ])
                                    ->first();
                                @endphp
                                @if ($branch_row)
                                    {{ $branch_row->name }}
                                @endif
                            @endif
                        </th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="6">GENERAL LEDGER</th>
                    </tr>
                    <tr>
                        <th colspan="6">PERIOD:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
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
                        $general_journal_date_tmp = '';
                        $general_journal_no_tmp = '';

                        $branches = \App\Models\Mst_branch::when($branch_id!=0, function($q) use($branch_id){
                            $q->where([
                                'id'=>$branch_id,
                            ]);
                        })
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('name','ASC')
                        ->get();

                        $coas = \App\Models\Mst_coa::where([
                            'id'=>$coa_id,
                            'active'=>'Y',
                        ])
                        ->first();
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ ($coas?$coas->coa_code_complete:'') }}</td>
                        <td style="text-align: center;">{{ ($coas?$coas->coa_name:'') }}</td>
                        <td colspan="3">&nbsp;</td>
                        <td style="text-align: center;">{{ date("d-M-Y") }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DATE</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DOCUMENT NO</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DESCRIPTION</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DEBIT</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">CREDIT</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">SALDO</td>
                    </tr>
                    @php
                        // cek beginning balance per bulan
                        $beginning_balance_amount = 0;
                        $beginning_balance_date = '';
                        $qBeginning_balance = \App\Models\Tx_coa_beginning_balance::where([
                            'coa_id'=>$coa_id,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('created_at<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].' 0:0:5\'')
                        ->orderBy('created_at','DESC')
                        ->first();
                        if ($qBeginning_balance){
                            $beginning_balance_amount = $qBeginning_balance->beginning_balance;
                            $beginning_balance_date = $qBeginning_balance->created_at;
                        }else{
                            $qBeginning_balance = \App\Models\Mst_coa::where([
                                'id'=>$coa_id,
                            ])
                            ->whereRaw('beginning_balance_date<=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->first();
                            if ($qBeginning_balance){
                                $beginning_balance_amount = $qBeginning_balance->beginning_balance_amount;
                                $beginning_balance_date = $qBeginning_balance->beginning_balance_date;
                            }
                        }

                        // cek apakah ada jurnal di antara tanggal beginning-balance hingga tanggal mulai
                        $debitX = 0;
                        $creditX = 0;
                        $branch_idx = $branch_id;
                        $allJdx = [];
                        if ($beginning_balance_date!=''){
                            $genJdx = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                            ->leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                            ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                            ->select(
                                'tx_gj.module_no',
                                'tx_general_journal_details.debit as debit',
                                'tx_general_journal_details.kredit as kredit',
                                'usr_d.branch_id as branch_id',
                            )
                            ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_general_journal_details.coa_id'=>$coa_id,
                                'tx_general_journal_details.active'=>'Y',
                                // 'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.is_draft'=>'N',
                                'tx_gj.active'=>'Y',
                            ])
                            ->whereRaw('tx_gj.general_journal_date>=\''.$beginning_balance_date.'\'')
                            ->whereRaw('tx_gj.general_journal_date<=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->orderBy('tx_gj.general_journal_date','DESC');

                            $lokJdx = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                            ->leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                            ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                            ->select(
                                'tx_lj.module_no',
                                'tx_lokal_journal_details.debit as debit',
                                'tx_lokal_journal_details.kredit as kredit',
                                'usr_d.branch_id as branch_id',
                            )
                            ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$coa_id,
                                'tx_lokal_journal_details.active'=>'Y',
                                // 'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.is_draft'=>'Y',
                                'tx_lj.active'=>'Y',
                            ])
                            ->whereRaw('tx_lj.general_journal_date>=\''.$beginning_balance_date.'\'')
                            ->whereRaw('tx_lj.general_journal_date<=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->orderBy('tx_lj.general_journal_date','DESC');

                            $allJdx = $lokJdx->union($genJdx)
                            ->get();
                        }
                    @endphp
                    @foreach ($allJdx as $journalx)
                        @php
                            $branch_id = 0;
                        @endphp
                        @if (strpos("J-".$journalx->module_no,env('P_FAKTUR'))>0)
                            {{-- faktur --}}
                            @php
                                $qFaktur = \App\Models\Tx_delivery_order::where([
                                    'delivery_order_no'=>$journalx->module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qFaktur){$branch_id = $qFaktur->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_NOTA_RETUR'))>0)
                            {{-- nota retur --}}
                            @php
                                $qNotaRetur = \App\Models\Tx_nota_retur::where([
                                    'nota_retur_no'=>$journal-x>module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qNotaRetur){$branch_id = $qNotaRetur->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_NOTA_PENJUALAN'))>0)
                            {{-- nota penjualan --}}
                            @php
                                $qNotaPenjualan = \App\Models\Tx_delivery_order_non_tax::where([
                                    'delivery_order_no'=>$journalx->module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qNotaPenjualan){$branch_id = $qNotaPenjualan->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_RETUR'))>0)
                            {{-- retur --}}
                            @php
                                $qRetur = \App\Models\Tx_nota_retur_non_tax::where([
                                    'nota_retur_no'=>$journalx->module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qRetur){$branch_id = $qRetur->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_RECEIPT_ORDER'))>0)
                            {{-- receipt order --}}
                            @php
                                $qRO = \App\Models\Tx_receipt_order::where([
                                    'receipt_no'=>$journalx->module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qRO){$branch_id = $qRO->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_PAYMENT_RECEIPT'))>0)
                            {{-- payment receipt / pembayaran customer --}}
                            @php
                                $qPembCust = \App\Models\Tx_payment_receipt::leftJoin('userdetails as usr_d','tx_payment_receipts.created_by','=','usr_d.user_id')
                                ->select(
                                    'usr_d.branch_id',
                                )
                                ->where([
                                    'tx_payment_receipts.payment_receipt_no'=>$journalx->module_no,
                                    'usr_d.branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qPembCust){$branch_id = $qPembCust->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_PAYMENT_VOUCHER'))>0)
                            {{-- payment voucher / pembayaran supplier --}}
                            @php
                                $qPembSupp = \App\Models\Tx_payment_voucher::leftJoin('userdetails as usr_d','tx_payment_vouchers.created_by','=','usr_d.user_id')
                                ->select(
                                    'usr_d.branch_id',
                                )
                                ->where([
                                    'tx_payment_vouchers.payment_voucher_no'=>$journalx->module_no,
                                    'usr_d.branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qPembSupp){$branch_id = $qPembSupp->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_STOCK_ADJUSTMENT'))>0)
                            {{-- stock adjusment --}}
                            @php
                                $qStockAdj = \App\Models\Tx_stock_adjustment::where([
                                    'stock_adj_no'=>$journalx->module_no,
                                    'branch_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qStockAdj){$branch_id = $qStockAdj->branch_id;}
                            @endphp
                        @endif
                        @if (strpos("J-".$journalx->module_no,env('P_STOCK_TRANSFER'))>0)
                            {{-- stock transfer --}}
                            @php
                                $qStockTrf = \App\Models\Tx_stock_transfer::where([
                                    'stock_transfer_no'=>$journalx->module_no,
                                    'branch_to_id'=>$branch_idx,
                                ])
                                ->first();
                                if ($qStockTrf){$branch_id = $qStockTrf->branch_to_id;}
                            @endphp
                        @endif
                        @if ($branch_id==0)
                            @php
                                $branch_id = $journalx->branch_id;
                            @endphp
                        @endif
                        @if ($branch_id==$branch_idx)
                            @php
                                $debitX += $journalx->debit;
                                $creditX += $journalx->kredit;
                            @endphp
                        @endif
                    @endforeach
                    @php
                        $last_beginning_balance_amount = $beginning_balance_amount+($debitX-$creditX);
                    @endphp
                    <tr>
                        <td style="text-align: center;border-left:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;">&nbsp;</td>
                        <td style="text-align: left;">Saldo awal</td>
                        <td style="text-align: right;">&nbsp;</td>
                        <td style="text-align: right;">&nbsp;</td>
                        <td style="text-align: right;border-right:1px solid black;">{{ $last_beginning_balance_amount }}</td>
                    </tr>
                    @foreach ($branches as $branch)
                        @php
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
                            ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_general_journal_details.coa_id'=>$coa_id,
                                'tx_general_journal_details.active'=>'Y',
                                // 'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.is_draft'=>'N',
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
                            ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$coa_id,
                                'tx_lokal_journal_details.active'=>'Y',
                                // 'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.is_draft'=>'N',
                                'tx_lj.active'=>'Y',
                            ])
                            ->whereRaw('tx_lj.general_journal_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_lj.general_journal_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->orderBy('tx_lj.general_journal_date','DESC');

                            $allJd = $lokJd->union($genJd)
                            ->orderBy('general_journal_date', 'ASC')
                            ->get();
                        @endphp
                        @if ($allJd)
                            @foreach ($allJd as $journal)
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
                                    <tr>
                                        @if ($journal->debit>0)
                                            @php
                                                $last_beginning_balance_amount = $last_beginning_balance_amount+$journal->debit;
                                            @endphp
                                        @endif
                                        @if ($journal->kredit>0)
                                            @php
                                                $last_beginning_balance_amount = $last_beginning_balance_amount-$journal->kredit;
                                            @endphp
                                        @endif
                                        <td style="text-align: center;border-left:1px solid black;">{{ ($general_journal_date_tmp!=$journal->general_journal_date?date_format(date_create($journal->general_journal_date),"d-M-Y"):'') }}</td>
                                        <td style="text-align: center;">{{ ($general_journal_no_tmp!=$journal->general_journal_no?$journal->general_journal_no:'') }}</td>
                                        <td style="text-align: left;">{{ $journal->description }}</td>
                                        <td style="text-align: right;">{{ $journal->debit }}</td>
                                        <td style="text-align: right;">{{ $journal->kredit }}</td>
                                        <td style="text-align: right;border-right:1px solid black;">{{ $last_beginning_balance_amount }}</td>
                                        @php
                                            $totalDebit += $journal->debit;
                                            $totalKredit += $journal->kredit;
                                            $general_journal_date_tmp = $journal->general_journal_date;
                                            $general_journal_no_tmp = $journal->general_journal_no;
                                        @endphp
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ $totalDebit }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ $totalKredit }}</td>
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
