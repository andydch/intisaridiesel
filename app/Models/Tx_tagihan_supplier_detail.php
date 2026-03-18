<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_tagihan_supplier_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_supplier_id',        
        'receipt_order_id',        
        'total_price_per_ro',        
        'is_vat_per_ro',        
        'active',
        'created_by',
        'updated_by'
    ];

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
