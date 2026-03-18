<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_receipt_order_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_order_id',
        'po_mo_no',
        'po_mo_id',
        'po_mo_part_id',
        'part_id',
        'qty',
        'qty_on_po',
        'part_price',
        'final_fob',
        'final_cost',
        'avg_cost',
        'total_price',
        'total_fob_price',
        'is_partial_received',
        'active',
        'created_by',
        'updated_by'
    ];

    public function receipt_order()
    {
        return $this->belongsTo(Tx_receipt_order::class, 'receipt_order_id', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
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
