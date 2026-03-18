<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Mst_global extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'data_cat',
        'title_ind',
        'title_eng',
        'slug',
        'order_no',
        'notes',
        'small_desc_ind',
        'small_desc_eng',
        'long_desc_ind',
        'long_desc_eng',
        'string_val',
        'numeric_val',
        'active',
        'created_by',
        'updated_by'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['data_cat', 'title_ind']
            ]
        ];
    }
}
