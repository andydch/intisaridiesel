<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_general_journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_journal_no',
        'general_journal_date',
        'total_debit',
        'total_kredit',
        'module_no',
        'automatic_journal_id',
        'draft_at',
        'draft_to_created_at',
        'is_draft',
        'is_wt_for_appr',
        'who_appr',
        'approved_at',
        'status_appr',
        'general_journal_date_old',
        'total_debit_old',
        'total_kredit_old',
        'total_debit_new',
        'total_kredit_new',
        'active',
        'created_by',
        'updated_by'
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'who_appr', 'id');
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
