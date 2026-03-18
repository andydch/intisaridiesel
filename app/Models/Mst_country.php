<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_country extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'country_name',
        'active',
        'created_by',
        'updated_by'
    ];
}
