<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mst_branch;
use App\Models\Mst_coa;
use App\Models\Tx_coa_beginning_balance;
use App\Models\Tx_delivery_order;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_general_journal_detail;
use App\Models\Tx_lokal_journal_detail;
use App\Models\Tx_nota_retur;
use App\Models\Tx_nota_retur_non_tax;
use App\Models\Tx_payment_receipt;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_receipt_order;
use App\Models\Tx_stock_adjustment;
use App\Models\Tx_stock_transfer;

class BeginningBalanceJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenBalance:BeginningBalanceAmountPerMonth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bentuk beginning balance tiap akhir bulan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $br = PHP_EOL;

        $date_exec_beginning_balance_this_month = now();
        $date_exec_beginning_balance_last_month = now();
        $date_exec_beginning_balance_next_month = now();
        date_add($date_exec_beginning_balance_last_month, date_interval_create_from_date_string("-1 months"));
        date_add($date_exec_beginning_balance_next_month, date_interval_create_from_date_string("1 months"));
        // echo date_format($date,"Y-m-d").'<br/>';

        $branches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->get();
        foreach($branches as $branch){
            $coas = Mst_coa::whereRaw('beginning_balance_amount>0')
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->get();
            foreach($coas as $coa){
                // beginning balance awal (digunakan jika beginning balance per bulan belum pernah terbentuk)
                $beginning_balance_date = (!is_null($coa->beginning_balance_date)?$coa->beginning_balance_date:date_format($date_exec_beginning_balance_this_month,"Y-m-d"));
                $beginning_balance_amount = $coa->beginning_balance_amount;

                // cek apakah beginning balance sudah terbentuk untuk bulan lalu
                $qBeginning_balance_last_month = Tx_coa_beginning_balance::where([
                    'coa_id'=>$coa->id,
                    'branch_id'=>$branch->id,
                    'active'=>'Y',
                ])
                ->whereRaw('created_at<\''.date_format($date_exec_beginning_balance_this_month,"Y-m").'-1 1:0:5\'')
                ->orderBy('created_at','DESC')
                ->first();
                if ($qBeginning_balance_last_month){
                    $beginning_balance_date = $qBeginning_balance_last_month->created_at;
                    $beginning_balance_amount = $qBeginning_balance_last_month->beginning_balance;
                }
                $arrBbDate = explode(" ",$beginning_balance_date);
                $last_beginning_balance_amount = $beginning_balance_amount;

                $genJd = Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                ->leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                ->select(
                    'tx_gj.module_no',
                    'tx_general_journal_details.debit as debit',
                    'tx_general_journal_details.kredit as kredit',
                    'usr_d.branch_id as branch_id',
                )
                ->where([
                    'tx_general_journal_details.coa_id'=>$coa->id,
                    'tx_general_journal_details.active'=>'Y',
                    'tx_gj.is_wt_for_appr'=>'N',
                    'tx_gj.active'=>'Y',
                ])
                ->whereRaw('tx_gj.general_journal_date>\''.$arrBbDate[0].' 23:59:55\'')
                ->whereRaw('tx_gj.general_journal_date<\''.date_format($date_exec_beginning_balance_next_month,"Y-m").'-01 23:59:55\'')
                // ->whereRaw('DATE_FORMAT(tx_gj.general_journal_date,"%Y-%m")=\''.date_format($date_exec_beginning_balance_this_month,"Y-m").'\'')
                ->orderBy('tx_gj.general_journal_date','DESC');

                $lokJd = Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                ->leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                ->select(
                    'tx_lj.module_no',
                    'tx_lokal_journal_details.debit as debit',
                    'tx_lokal_journal_details.kredit as kredit',
                    'usr_d.branch_id as branch_id',
                )
                ->where([
                    'tx_lokal_journal_details.coa_id'=>$coa->id,
                    'tx_lokal_journal_details.active'=>'Y',
                    'tx_lj.is_wt_for_appr'=>'N',
                    'tx_lj.active'=>'Y',
                ])
                ->whereRaw('tx_lj.general_journal_date>\''.$arrBbDate[0].' 23:59:55\'')
                ->whereRaw('tx_lj.general_journal_date<\''.date_format($date_exec_beginning_balance_next_month,"Y-m").'-01 23:59:55\'')
                // ->whereRaw('DATE_FORMAT(tx_lj.general_journal_date,"%Y-%m")=\''.date_format($date_exec_beginning_balance_this_month,"Y-m").'\'')
                ->orderBy('tx_lj.general_journal_date','DESC');

                $allJd = $lokJd->union($genJd)
                ->get();
                foreach ($allJd as $journal){
                    $branch_id = 0;

                    if (strpos("J-".$journal->module_no,env('P_FAKTUR'))>0){
                        // faktur
                        $qFaktur = Tx_delivery_order::leftJoin('mst_customers as mst_c','tx_delivery_orders.customer_id','=','mst_c.id')
                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                        ->select(
                            'usr_s.branch_id',
                        )
                        ->where([
                            'tx_delivery_orders.delivery_order_no'=>$journal->module_no,
                            'usr_s.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qFaktur){$branch_id = $qFaktur->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_NOTA_RETUR'))>0){
                        // nota retur
                        $qNotaRetur = Tx_nota_retur::leftJoin('mst_customers as mst_c','tx_nota_returs.customer_id','=','mst_c.id')
                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                        ->select(
                            'usr_s.branch_id',
                        )
                        ->where([
                            'tx_nota_returs.nota_retur_no'=>$journal->module_no,
                            'usr_s.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qNotaRetur){$branch_id = $qNotaRetur->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_NOTA_PENJUALAN'))>0){
                        // nota penjualan
                        $qNotaPenjualan = Tx_delivery_order_non_tax::leftJoin('mst_customers as mst_c','tx_delivery_order_non_taxes.customer_id','=','mst_c.id')
                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                        ->select(
                            'usr_s.branch_id',
                        )
                        ->where([
                            'tx_delivery_order_non_taxes.delivery_order_no'=>$journal->module_no,
                            'usr_s.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qNotaPenjualan){$branch_id = $qNotaPenjualan->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_RETUR'))>0){
                        // retur
                        $qRetur = Tx_nota_retur_non_tax::leftJoin('mst_customers as mst_c','tx_nota_retur_non_taxes.customer_id','=','mst_c.id')
                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                        ->select(
                            'usr_s.branch_id',
                        )
                        ->where([
                            'tx_nota_retur_non_taxes.nota_retur_no'=>$journal->module_no,
                            'usr_s.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qRetur){$branch_id = $qRetur->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_RECEIPT_ORDER'))>0){
                        // receipt order
                        $qRO = Tx_receipt_order::where([
                            'receipt_no'=>$journal->module_no,
                            'branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qRO){$branch_id = $qRO->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_PAYMENT_RECEIPT'))>0){
                        // payment receipt / pembayaran customer
                        $qPembCust = Tx_payment_receipt::leftJoin('mst_customers as mst_c','tx_payment_receipts.customer_id','=','mst_c.id')
                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                        ->select(
                            'usr_s.branch_id',
                        )
                        ->where([
                            'tx_payment_receipts.payment_receipt_no'=>$journal->module_no,
                            'usr_s.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qPembCust){$branch_id = $qPembCust->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_PAYMENT_VOUCHER'))>0){
                        // payment voucher / pembayaran supplier
                        $qPembSupp = Tx_payment_voucher::leftJoin('userdetails as usr_d','tx_payment_vouchers.created_by','=','usr_d.user_id')
                        ->select(
                            'usr_d.branch_id',
                        )
                        ->where([
                            'tx_payment_vouchers.payment_voucher_no'=>$journal->module_no,
                            'usr_d.branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qPembSupp){$branch_id = $qPembSupp->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_STOCK_ADJUSTMENT'))>0){
                        // stock adjusment
                        $qStockAdj = Tx_stock_adjustment::where([
                            'stock_adj_no'=>$journal->module_no,
                            'branch_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qStockAdj){$branch_id = $qStockAdj->branch_id;}
                    }
                    if (strpos("J-".$journal->module_no,env('P_STOCK_TRANSFER'))>0){
                        // stock transfer
                        $qStockTrf = Tx_stock_transfer::where([
                            'stock_transfer_no'=>$journal->module_no,
                            'branch_to_id'=>$branch->id,
                        ])
                        ->first();
                        if ($qStockTrf){$branch_id = $qStockTrf->branch_to_id;}
                    }
                    if ($branch_id==0){
                        $branch_id = $journal->branch_id;
                    }

                    if ($branch_id==$branch->id){
                        if ($journal->debit>0){
                            $last_beginning_balance_amount = $last_beginning_balance_amount+$journal->debit;
                        }
                        if ($journal->kredit>0){
                            $last_beginning_balance_amount = $last_beginning_balance_amount-$journal->kredit;
                        }
                    }
                }

                echo $coa->coa_name.'['.$coa->id.']: '.$last_beginning_balance_amount.$br;

                $qIsExists = Tx_coa_beginning_balance::where([
                    'coa_id'=>$coa->id,
                    'branch_id'=>$branch->id,
                ])
                ->whereRaw('DATE_FORMAT(created_at,"%Y-%m")=\''.date_format($date_exec_beginning_balance_this_month,"Y-m").'\'')
                ->first();
                if(!$qIsExists){
                    $ins = Tx_coa_beginning_balance::create([
                        'coa_id'=>$coa->id,
                        'branch_id'=>$branch->id,
                        'beginning_balance'=>$last_beginning_balance_amount,
                        'active'=>'Y',
                        'created_by'=>1,
                        'updated_by'=>1,
                    ]);
                }
            }
        }

        return Command::SUCCESS;
    }
}
