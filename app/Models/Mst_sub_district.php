<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_sub_district extends Model
{
    use HasFactory;

    protected $table = 'mst_sub_districts';
    protected $fillable = [
        'sub_district_name',
        'district_id',
        'post_code',
        'active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'post_code' => 'string',
    ];

    public function district()
    {
        return $this->belongsTo(Mst_district::class, 'district_id', 'id');
    }
}
