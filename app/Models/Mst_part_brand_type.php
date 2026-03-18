<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_part_brand_type extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'brand_id',
        'brand_type_id',
        'active',
        'created_by',
        'updated_by',
    ];

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Mst_global::class, 'brand_id', 'id');
    }

    public function brand_type()
    {
        return $this->belongsTo(Mst_brand_type::class, 'brand_type_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
