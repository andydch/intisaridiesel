<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_province extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'province_name',
        'country_id',
        'active',
        'created_by',
        'updated_by'
    ];

    public function country()
    {
        return $this->belongsTo(Mst_country::class, 'country_id', 'id');
    }
}
