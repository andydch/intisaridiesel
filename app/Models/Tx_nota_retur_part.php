<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_nota_retur_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'nota_retur_id',
        'sales_order_part_id',
        'part_id',
        'qty_retur',
        'qty_do',
        'final_price',
        'total_price',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function nota_retur()
    {
        return $this->belongsTo(Tx_nota_retur::class, 'nota_retur_id', 'id');
    }

    public function so_part()
    {
        return $this->belongsTo(Tx_sales_order_part::class, 'sales_order_part_id', 'id');
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
