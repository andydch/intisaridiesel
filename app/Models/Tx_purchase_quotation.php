<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_no',
        'quotation_date',
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
        'total_qty',
        'pic_idx',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'header',
        'footer',
        'remark',
        'active',
        'created_by',
        'updated_by'
    ];

    public function purchase_order()
    {
        return $this->hasOne(Tx_purchase_order::class, 'quotation_id', 'id');
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

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
