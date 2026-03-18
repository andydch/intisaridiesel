<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_customer_shipment_address extends Model
{
    use HasFactory;

    protected $table = 'mst_customer_shipment_address';
    protected $fillable = [
        'customer_id',
        'address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'phone',
        'post_code',
        'active',
        'created_by',
        'updated_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Mst_province::class, 'province_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(Mst_city::class, 'city_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(Mst_district::class, 'district_id', 'id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(Mst_sub_district::class, 'sub_district_id', 'id');
    }
}
