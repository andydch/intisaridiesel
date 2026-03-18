<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_tagihan_supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_supplier_no',        
        'tagihan_supplier_date',        
        'supplier_id',        
        'total_price',        
        'total_price_vat',        
        'grandtotal_price',        
        'is_vat',        
        'bank_id',        
        'active',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Mst_coa::class, 'bank_id', 'id');
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
