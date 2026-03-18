<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_acceptance_plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'acceptance_month',
        'bank_id',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'active',
        'created_by',
        'updated_by'
    ];

    public function bank()
    {
        return $this->belongsTo(Mst_coa::class, 'bank_id', 'id');
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
