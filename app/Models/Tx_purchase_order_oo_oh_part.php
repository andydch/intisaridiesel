<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_order_oo_oh_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_order_part_id',
        'part_id',
        'branch_id',
        'last_OO_PO_created',
        'last_OH_PO_created',
        'last_OO_OH_PO_created',
        'last_OO_PO_approval',
        'last_OH_PO_approval',
        'last_OO_OH_PO_approval',
        'active',
        'created_by',
        'updated_by'
    ];

    public function purchase_order()
    {
        return $this->belongsTo(Tx_purchase_order::class, 'purchase_order_id', 'id');
    }

    public function purchase_order_part()
    {
        return $this->belongsTo(Tx_purchase_order_part::class, 'purchase_order_part_id', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
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
