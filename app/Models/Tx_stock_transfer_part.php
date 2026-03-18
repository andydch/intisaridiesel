<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_transfer_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'part_id',
        'qty',
        'last_avg_cost',
        'active',
        'created_by',
        'updated_by'
    ];

    public function stocktransfer()
    {
        return $this->belongsTo(Tx_stock_transfer::class, 'stock_transfer_id', 'id');
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
