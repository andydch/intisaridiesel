<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_nota_retur_part_non_tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'nota_retur_id',
        'surat_jalan_part_id',
        'part_id',
        'qty_retur',
        'qty_do',
        'final_price',
        'total_price',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    public function nota_retur_non_tax()
    {
        return $this->belongsTo(Tx_nota_retur_non_tax::class, 'nota_retur_id', 'id');
    }

    public function surat_jalan()
    {
        return $this->belongsTo(Tx_surat_jalan_part::class, 'surat_jalan_part_id', 'id');
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
