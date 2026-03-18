<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_sales_quotation_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_quotation_id',
        'part_id',
        'qty',
        'price_part',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function sales_quotation()
    {
        return $this->belongsTo(Tx_sales_quotation::class, 'sales_quotation_id', 'id');
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
