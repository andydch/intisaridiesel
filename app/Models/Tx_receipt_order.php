<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_receipt_order extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'receipt_date',
        'po_or_pm_no',
        'journal_type_id',
        'supplier_id',
        'supplier_type_id',
        'supplier_entity_type_id',
        'supplier_name',
        'currency_id',
        'total_qty',
        'total_before_vat',
        'total_before_vat_rp',
        'total_vat',
        'total_vat_rp',
        'total_after_vat',
        'total_after_vat_rp',
        'branch_id',
        'courier_id',
        'courier_type',
        'invoice_no',
        'invoice_amount',
        'exchange_rate',
        'exc_rate_for_vat',
        'bea_masuk',
        'import_shipping_cost',
        'bl_no',
        'vessel_no',
        'weight_type_id01',
        'weight_type_id02',
        'gross_weight',
        'measurement',
        'remark',
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

    public function currency()
    {
        return $this->belongsTo(Mst_global::class, 'currency_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function weight_type_01()
    {
        return $this->belongsTo(Mst_global::class, 'weight_type_id01', 'id');
    }

    public function weight_type_02()
    {
        return $this->belongsTo(Mst_global::class, 'weight_type_id02', 'id');
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
