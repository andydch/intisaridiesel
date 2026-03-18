<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_tx_qty_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'qty',
        'branch_id',
        'avg_cost',
        'created_by',
        'updated_by'
    ];
    public $timestamps = false;

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }
}
