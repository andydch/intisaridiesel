<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_delivery_order_non_tax_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'sales_order_id',
        'sales_order_part_id',
        'part_id',
        'qty',
        'qty_so',
        'final_price',
        'total_price',
        'description',
        'is_partial_delivered',
        'active',
        'created_by',
        'updated_by'
    ];

    public function delivery_order()
    {
        return $this->belongsTo(Tx_delivery_order_non_tax::class, 'delivery_order_id', 'id');
    }

    public function sales_order()
    {
        return $this->belongsTo(Tx_surat_jalan::class, 'sales_order_id', 'id');
    }

    public function sales_order_part()
    {
        return $this->belongsTo(Tx_surat_jalan_part::class, 'sales_order_part_id', 'id');
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
