<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_voucher_invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'receipt_order_id',
        'invoice_no',
        'description',
        'total_payment',
        'total_payment_after_vat',
        'total_payment_before_retur',
        'total_payment_before_retur_after_vat',
        'is_full_payment',
        'active',
        'created_by',
        'updated_by'
    ];

    public function pv()
    {
        return $this->belongsTo(Tx_payment_voucher::class, 'payment_voucher_id', 'id');
    }

    public function receipt_order()
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
