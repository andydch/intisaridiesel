<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_kwitansi_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'kwitansi_id',
        'np_id',
        'nota_penjualan_no',
        'delivery_order_date',
        'sj_no',
        'total',
        'active',
        'updated_by',
        'created_by',
    ];

    public function kwitansi()
    {
        return $this->belongsTo(Tx_kwitansi::class, 'kwitansi_id', 'id');
    }

    public function nota_penjualan()
    {
        return $this->belongsTo(Tx_delivery_order_non_tax::class, 'np_id', 'id');
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
