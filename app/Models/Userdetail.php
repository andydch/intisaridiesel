<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'initial',
        'date_of_birth',
        'address',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'position',
        'section_id',
        'profile_pic',
        'phone1',
        'phone2',
        'id_no',
        'signage_pic',
        'branch_id',
        'is_salesman',
        'is_director',
        'is_branch_head',
        'active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Mst_country::class, 'country_id', 'id');
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

    public function sub_district()
    {
        return $this->belongsTo(Mst_sub_district::class, 'sub_district_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function section_user()
    {
        return $this->belongsTo(Mst_global::class, 'section_id', 'id');
    }
}
