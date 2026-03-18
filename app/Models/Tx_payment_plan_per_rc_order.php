<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_plan_per_rc_order extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'plan_date',
        'plan_pay',
        'receipt_order_id',
        'active',
        'created_by',
        'updated_by'
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(Tx_payment_plan::class, 'payment_plan_id', 'id');
    }

    public function receiptOrder()
    {
        return $this->belongsTo(Tx_receipt_order::class, 'receipt_order_id', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
