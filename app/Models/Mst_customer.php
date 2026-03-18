<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Mst_customer extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'entity_type_id',
        'customer_unique_code',
        'name',
        'slug',
        'office_address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'post_code',
        'cust_email',
        'branch_id',
        'phone1',
        'phone2',
        'pic1_name',
        'pic1_phone',
        'pic1_email',
        'pic2_name',
        'pic2_phone',
        'pic2_email',
        'npwp_no',
        'npwp_address',
        'npwp_province_id',
        'npwp_city_id',
        'npwp_district_id',
        'npwp_sub_district_id',
        'credit_limit',
        'limit_balance',
        'top',
        'salesman_id',
        'salesman_id2',
        'customer_status',
        'payment_status',
        'beginning_balance',
        'active',
        'created_by',
        'updated_by',
    ];

    public function entity_type()
    {
        return $this->belongsTo(Mst_global::class, 'entity_type_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Mst_province::class, 'province_id', 'id');
    }

    public function npwp_province()
    {
        return $this->belongsTo(Mst_province::class, 'npwp_province_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(Mst_city::class, 'city_id', 'id');
    }

    public function npwp_city()
    {
        return $this->belongsTo(Mst_city::class, 'npwp_city_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(Mst_district::class, 'district_id', 'id');
    }

    public function npwp_district()
    {
        return $this->belongsTo(Mst_district::class, 'npwp_district_id', 'id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(Mst_sub_district::class, 'sub_district_id', 'id');
    }

    public function npwp_subdistrict()
    {
        return $this->belongsTo(Mst_sub_district::class, 'npwp_sub_district_id', 'id');
    }

    public function salesman01()
    {
        return $this->belongsTo(Userdetail::class, 'salesman_id', 'user_id');
    }

    public function salesman02()
    {
        return $this->belongsTo(User::class, 'salesman_id2', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['customer_unique_code', 'name']
            ]
        ];
    }
}
