<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_assembly extends Model
{
    use HasFactory;

    protected $table = 'tx_stock_assemblys';
    protected $fillable = [
        'stock_assembly_no',
        'stock_assembly_date',
        'part_id',
        'qty',
        'branch_id',
        'final_cost',
        'avg_cost',
        'remark',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'active',
        'created_by',
        'updated_by'
    ];

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
