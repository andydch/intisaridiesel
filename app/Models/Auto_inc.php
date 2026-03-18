<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auto_inc extends Model
{
    use HasFactory;

    protected $fillable = [
        'identity_name',
        'id_auto_inc'
    ];
}
