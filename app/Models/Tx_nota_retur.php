<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_nota_retur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nota_retur_no',
        'nota_retur_date',
        'delivery_order_id',
        'customer_id',
        'customer_entity_type_id',
        'customer_name',
        'branch_id',
        'remark',
        'total_qty',
        'total_before_vat',
        'total_after_vat',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'is_vat',
        'vat_val',
        'active',
        'approved_by',
        'canceled_by',
        'approved_at',
        'canceled_at',
        'created_by',
        'updated_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function delivery_order()
    {
        return $this->belongsTo(Tx_delivery_order::class, 'delivery_order_id', 'id');
    }

    public function customer_entity_type()
    {
        return $this->belongsTo(Mst_global::class, 'customer_entity_type_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by', 'id');
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
