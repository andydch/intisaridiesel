<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_surat_jalan_part extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jalan_id',
        'part_id',
        'part_no',
        'qty',
        'price',
        'last_avg_cost',
        'desc',
        'active',
        'created_by',
        'updated_by'
    ];

    public function order()
    {
        return $this->belongsTo(Tx_surat_jalan::class, 'surat_jalan_id', 'id');
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
