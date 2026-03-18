<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_adjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adj_no',
        'stock_adj_date',
        'branch_id',
        'remark',
        'total',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'active',
        'created_by',
        'updated_by',
    ];

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
