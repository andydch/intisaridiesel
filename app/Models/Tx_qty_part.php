<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_qty_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'qty',
        'min_qty',
        'max_qty',
        'branch_id',
        'avg_cost_first',
        'created_by',
        'updated_by'
    ];

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }
}
