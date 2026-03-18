<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_receipt_invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_receipt_id',
        'invoice_id',
        'invoice_no',
        'description',
        'total_payment',
        'total_payment_after_vat',
        'total_payment_full',
        'total_payment_full_after_vat',
        'is_full_payment',
        'is_vat',
        'active',
        'created_by',
        'updated_by'
    ];

    public function payment_receipt()
    {
        return $this->belongsTo(Tx_payment_receipt::class, 'payment_receipt_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Tx_invoice::class, 'invoice_id', 'id');
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
