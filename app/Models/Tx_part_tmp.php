<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_part_tmp extends Model
{
    use HasFactory;

    protected $table = 'tx_part_tmp';

    protected $fillable = [
        'part_id',
        'branch_id',
        'qty',
        'avg_cost',
    ];
}
