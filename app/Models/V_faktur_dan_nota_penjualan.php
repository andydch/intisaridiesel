<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V_faktur_dan_nota_penjualan extends Model
{
    use HasFactory;

    protected $table = 'v_faktur_dan_nota_penjualan';

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
