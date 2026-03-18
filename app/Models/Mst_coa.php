<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_coa extends Model
{
    use HasFactory;

    protected $fillable = [
        'coa_level',
        'coa_code',
        'coa_code_complete',
        'coa_name',
        'coa_parent',
        'branch_id',
        'is_master_coa',
        'is_balance_sheet',
        'is_profit_loss',
        'local',
        'beginning_balance_date',
        'beginning_balance_amount',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'active',
        'created_by',
        'updated_by'
    ];

    public function coaParent()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_parent', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
