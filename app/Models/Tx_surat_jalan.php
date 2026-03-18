<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_surat_jalan extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jalan_no',
        'sales_quotation_id',
        'customer_doc_no',
        'surat_jalan_date',
        'surat_jalan_expired_date',
        'customer_id',
        'cust_entity_type',
        'cust_name',
        'cust_office_address',
        'cust_country_id',
        'cust_province_id',
        'cust_city_id',
        'cust_district_id',
        'cust_sub_district_id',
        'cust_shipment_address',
        'post_code',
        'branch_id',
        'pic_id',
        'pic_name',
        'cust_unit_no',
        'total_qty',
        'total',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'approved_by',
        'approved_at',
        'canceled_by',
        'canceled_at',
        'reason',
        'need_approval',
        'remark',
        'courier_id',
        'courier_type',
        'number_of_prints',
        'active',
        'created_by',
        'updated_by'
    ];

    public function delivery_order()
    {
        return $this->hasOne(Tx_delivery_order_non_tax_part::class,'sales_order_id','id');
    }

    public function sales_quotation()
    {
        return $this->belongsTo(Tx_sales_quotation::class, 'sales_quotation_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function sub_district()
    {
        return $this->belongsTo(Mst_sub_district::class, 'cust_sub_district_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(Mst_district::class, 'cust_district_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(Mst_city::class, 'cust_city_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Mst_province::class, 'cust_province_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Mst_country::class, 'cust_country_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function customer_shipment()
    {
        return $this->belongsTo(Mst_customer_shipment_address::class, 'cust_shipment_address', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'cust_entity_type', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Mst_company::class, 'company_id', 'id');
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
