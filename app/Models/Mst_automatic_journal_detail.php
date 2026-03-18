<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_automatic_journal_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_journal_id',
        'method_id',
        'branch_id',
        'branch_in_id',
        'coa_code_id',
        'desc',
        'debet_or_credit',
        'order_no',
        'active',
        'created_by',
        'updated_by'
    ];

    public function auto_jurnal()
    {
        return $this->belongsTo(Mst_automatic_journal::class, 'auto_journal_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_code_id', 'id');
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
