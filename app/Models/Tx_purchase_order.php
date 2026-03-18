<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_order extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_no',
        'quotation_id',
        'purchase_date',
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
        'currency_id',
        'branch_id',
        'branch_address',
        'courier_id',
        'courier_type',
        'est_supply_date',
        'approved_status',
        'rejected_reason',
        'approved_by',
        'approved_at',
        'canceled_by',
        'canceled_at',
        'draft_at',
        'draft_to_created_at',
        'is_draft',
        'is_vat',
        'vat_val',
        'director_updated_by',
        'director_updated_at',
        'active',
        'created_by',
        'updated_by'
    ];

    public function receipt_order()
    {
        return $this->hasOne(Tx_receipt_order_part::class, 'po_mo_no', 'purchase_no');
    }

    public function quotation()
    {
        return $this->hasOne(Tx_purchase_quotation::class, 'id', 'quotation_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function supplier_type()
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

    public function cancelBy()
    {
        return $this->belongsTo(User::class, 'canceled_by', 'id');
    }

    public function approved_by_info()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
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
