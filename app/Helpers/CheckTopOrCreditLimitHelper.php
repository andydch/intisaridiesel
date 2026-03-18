<?php

namespace App\Helpers;

use App\Models\V_so_sj;
use App\Models\V_invoice;
use App\Models\Tx_sales_order;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Mst_customer;

class CheckTopOrCreditLimitHelper
{
    public static function checkAll($cust, $so_sj_no, $totalDpp)
    {
        $msgResult = '';

        // cek limit
        $totTagihan = V_invoice::where('customer_id','=',$cust->id)
        ->sum('tagihan_dpp');
        
        $totReceipt = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts AS tx_pr', 'tx_payment_receipt_invoices.payment_receipt_id', '=', 'tx_pr.id')
        ->where('tx_pr.customer_id','=',$cust->id)
        ->where('tx_pr.is_draft','=','N')
        ->where('tx_pr.active','=','Y')
        ->sum('tx_payment_receipt_invoices.total_payment');
        if ($cust->credit_limit < ($totTagihan - $totReceipt + $totalDpp)){
            return 'CUSTOMER ANDA OVERLIMIT';
        }
        // cek limit

        // cek top
        $qInv = V_invoice::where('customer_id','=',$cust->id)
        ->whereRaw('invoice_no NOT IN (
            SELECT tx_pri.invoice_no FROM tx_payment_receipt_invoices AS tx_pri 
            LEFT JOIN tx_payment_receipts AS tx_pr ON tx_pri.payment_receipt_id = tx_pr.id 
            WHERE tx_pri.active=\'Y\'
            AND tx_pr.customer_id = '.$cust->id.'
            AND tx_pr.is_draft = \'N\'
            AND tx_pr.active = \'Y\')'
        ) 
        ->orderBy('invoice_no', 'ASC')
        ->first();
        if ($qInv){
            $qMstCust = Mst_customer::where('id','=',$cust->id)
            ->first();
            if ($qMstCust){                
                if (date_add(date_create(date("Y-m-d")), date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")) > 
                    date_add(date_create($qInv->invoice_date), date_interval_create_from_date_string($qMstCust->top." days"))){
                    return 'CUSTOMER ANDA OVERDUE';
                }
            }
        }
        // cek top

        return $msgResult;
    }
}
