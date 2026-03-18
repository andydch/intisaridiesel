<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_invoice_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'part_id',
        'qty',
        'final_price',
        'total_price',
        'active',
        'created_by',
        'updated_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Tx_invoice::class, 'invoice_id', 'id');
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
