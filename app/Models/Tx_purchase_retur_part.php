<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_retur_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_retur_id',
        'part_id',
        'qty',
        'qty_retur',
        'final_cost',
        'total_retur',
        'total_price',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function purchase_retur()
    {
        return $this->belongsTo(Tx_purchase_retur::class, 'purchase_retur_id', 'id');
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
