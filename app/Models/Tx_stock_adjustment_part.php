<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_adjustment_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adj_id',
        'part_id',
        'adjustment',
        'qty_oh',
        'qty_oh_adjustment',
        'qty_so',
        'avg_cost',
        'total',
        'notes',
        'active',
        'created_by',
        'updated_by',
    ];

    public function stock_adj()
    {
        return $this->belongsTo(Tx_stock_adjustment::class, 'stock_adj_id', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
