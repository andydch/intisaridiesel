<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_purchase_memo_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_id',
        'part_id',
        'qty',
        'price',
        'description',
        'active',
        'created_by',
        'updated_by'
    ];

    public function memo()
    {
        return $this->belongsTo(Tx_purchase_memo::class, 'memo_id', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function total_qty()
    {
    	return $this->hasOne(Tx_qty_part::class,'part_id','part_id');
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
