<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_disassembly extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_disassembly_no',
        'stock_disassembly_date',
        'part_id',
        'qty',
        'branch_id',
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

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
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
