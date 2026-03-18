<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_city extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'city_name',
        'country_id',
        'province_id',
        'city_type',
        'postal_code',
        'active',
        'created_by',
        'updated_by'
    ];

    public function province()
    {
        return $this->belongsTo(Mst_province::class, 'province_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Mst_country::class, 'country_id', 'id');
    }
}
