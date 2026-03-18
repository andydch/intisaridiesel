<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Mst_branch extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'initial',
        'name',
        'slug',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'address',
        'post_code',
        'phone1',
        'phone2',
        'active',
        'created_by',
        'updated_by'
    ];

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
                'source' => ['initial', 'name']
            ]
        ];
    }
}
