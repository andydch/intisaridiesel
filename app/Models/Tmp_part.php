<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tmp_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_number',
        'branch_initial',
    ];
}
