<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_invoice_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'fk_id',
        'delivery_order_no',
        'delivery_order_date',
        'tax_invoice_id',
        'fp_no',
        'total',
        'vat',
        'grand_total',
        'active',
        'created_by',
        'updated_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Tx_invoice::class, 'invoice_id', 'id');
    }

    public function faktur()
    {
        return $this->belongsTo(Tx_delivery_order::class, 'fk_id', 'id');
    }

    public function tax_invoice()
    {
        return $this->belongsTo(Tx_tax_invoice::class, 'tax_invoice_id', 'id');
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
