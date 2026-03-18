<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_inquiry_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_inquiry_id',
        'part_name',
        'qty',
        'unit',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function purchase_inquiry()
    {
        return $this->belongsTo(Tx_purchase_inquiry::class, 'purchase_inquiry_id', 'id');
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
