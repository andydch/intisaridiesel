<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_memo extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_no',
        'memo_date',
        'supplier_id',
        'supplier_type_id',
        'supplier_entity_type_id',
        'supplier_name',
        'supplier_office_address',
        'supplier_country_id',
        'supplier_province_id',
        'supplier_city_id',
        'supplier_district_id',
        'supplier_sub_district_id',
        'supplier_post_code',
        'pic_idx',
        'total_qty',
        'total_before_vat',
        'total_after_vat',
        'branch_id',
        'branch_address',
        'is_draft',
        'is_vat',
        'vat_val',
        'active',
        'created_by',
        'updated_by',
        'canceled_by',
        'canceled_at',
        'draft_at',
        'draft_to_created_at',
        'remark',
        'is_received',
        'received_at'
    ];

    public function receipt_order()
    {
        return $this->hasOne(Tx_receipt_order::class, 'po_or_pm_no', 'memo_no');
    }

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

    public function supplier_country()
    {
        return $this->belongsTo(Mst_country::class, 'supplier_country_id', 'id');
    }

    public function supplier_province()
    {
        return $this->belongsTo(Mst_province::class, 'supplier_province_id', 'id');
    }

    public function supplier_city()
    {
        return $this->belongsTo(Mst_city::class, 'supplier_city_id', 'id');
    }

    public function supplier_district()
    {
        return $this->belongsTo(Mst_district::class, 'supplier_district_id', 'id');
    }

    public function supplier_sub_district()
    {
        return $this->belongsTo(Mst_sub_district::class, 'supplier_sub_district_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function cancelBy()
    {
        return $this->belongsTo(User::class, 'cancel_by', 'id');
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
