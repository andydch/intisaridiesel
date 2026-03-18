<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_sales_quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_quotation_no',
        'sales_quotation_date',
        'customer_id',
        'customer_type_id',
        'customer_entity_type_id',
        'customer_name',
        'customer_office_address',
        'customer_country_id',
        'customer_province_id',
        'customer_city_id',
        'customer_district_id',
        'customer_sub_district_id',
        'customer_post_code',
        'branch_id',
        'total_qty',
        'header',
        'footer',
        'remark',
        'pic_idx',
        'vat_val',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'cancel_by',
        'cancel_time',
        'active',
        'created_by',
        'updated_by'
    ];

    public function sales_order()
    {
        return $this->hasOne(Tx_sales_order::class, 'sales_quotation_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function sub_district()
    {
        return $this->belongsTo(Mst_sub_district::class, 'customer_sub_district_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(Mst_district::class, 'customer_district_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(Mst_city::class, 'customer_city_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Mst_province::class, 'customer_province_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Mst_country::class, 'customer_country_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
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
