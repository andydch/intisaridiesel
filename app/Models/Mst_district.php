<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_district extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'district_name',
        'city_id',
        'active',
        'created_by',
        'updated_by'
    ];

    public function city()
    {
        return $this->belongsTo(Mst_city::class, 'city_id', 'id');
    }
}
