<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_retur extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_retur_no',
        'purchase_retur_date',
        'supplier_id',
        'supplier_type_id',
        'supplier_entity_type_id',
        'supplier_name',
        'receipt_order_id',
        'currency_id',
        'exc_rate',
        'branch_id',
        'courier_id',
        'courier_type',
        'remark',
        'total_qty',
        'total_before_vat',
        'total_after_vat',
        'vat_val',
        'approved_by',
        'approved_at',
        'canceled_by',
        'canceled_at',
        'draft_at',
        'draft_to_created_at',
        'is_draft',
        'active',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function supplier_type_id()
    {
        return $this->belongsTo(Mst_global::class, 'supplier_type_id', 'id');
    }

    public function supplier_entity_type()
    {
        return $this->belongsTo(Mst_global::class, 'supplier_entity_type_id', 'id');
    }

    public function receipt_order()
    {
        return $this->belongsTo(Tx_receipt_order::class, 'receipt_order_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Mst_global::class, 'currency_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function courier()
    {
        return $this->belongsTo(Mst_courier::class, 'courier_id', 'id');
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
