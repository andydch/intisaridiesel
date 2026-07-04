<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_plan_per_rc_order extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'supplier_id',
        'tagihan_supplier_id',
        'tagihan_supplier_no',
        'plan_date',
        'plan_pay',
        // 'receipt_order_id',
        'payment_voucher_id',
        'payment_voucher_no',
        'actual_date',
        'actual_payment',
        'is_pv_approved',
        'active',
        'created_by',
        'updated_by'
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(Tx_payment_plan::class, 'payment_plan_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function tagihan_supplier()
    {
        return $this->belongsTo(Tx_tagihan_supplier::class, 'tagihan_supplier_id', 'id');
    }

    public function payment_voucher()
    {
        return $this->belongsTo(Tx_payment_voucher::class, 'payment_voucher_id', 'id');
    }

    // public function receiptOrder()
    // {
    //     return $this->belongsTo(Tx_receipt_order::class, 'receipt_order_id', 'id');
    // }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
