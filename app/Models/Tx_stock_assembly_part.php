<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_assembly_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_assembly_id',
        'part_id',
        'qty',
        'final_cost',
        'avg_cost',
        'active',
        'created_by',
        'updated_by'
    ];

    public function stock_assembly()
    {
        return $this->belongsTo(Tx_stock_assembly::class, 'stock_assembly_id', 'id');
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
