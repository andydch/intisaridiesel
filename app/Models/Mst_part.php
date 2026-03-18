<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Mst_part extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'part_number',
        'part_name',
        'slug',
        'part_type_id',
        'part_category_id',
        'brand_id',
        'weight',
        'weight_id',
        'quantity_type_id',
        'part_brand',
        'max_stock',
        'safety_stock',
        'price_list',
        'final_price',
        'avg_cost',
        'initial_cost',
        'final_cost',
        'total_cost',
        'total_sales',
        'fob_currency',
        'final_fob',
        'active',
        'created_by',
        'updated_by',
    ];

    public function part_type()
    {
        return $this->belongsTo(Mst_global::class, 'part_type_id', 'id');
    }

    public function part_category()
    {
        return $this->belongsTo(Mst_global::class, 'part_category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Mst_global::class, 'brand_id', 'id');
    }

    public function weight_unit()
    {
        return $this->belongsTo(Mst_global::class, 'weight_id', 'id');
    }

    public function quantity_type()
    {
        return $this->belongsTo(Mst_global::class, 'quantity_type_id', 'id');
    }

    public function fobCurr()
    {
        return $this->belongsTo(Mst_global::class, 'fob_currency', 'id');
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
                'source' => 'part_name'
            ]
        ];
    }
}
