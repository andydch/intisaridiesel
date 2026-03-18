<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_quotation_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'part_id',
        'qty',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function quotation()
    {
        return $this->belongsTo(Tx_purchase_quotation::class, 'quotation_id', 'id');
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
