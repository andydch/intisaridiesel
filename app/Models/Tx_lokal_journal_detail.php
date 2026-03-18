<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_lokal_journal_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'lokal_journal_id',
        'coa_id',
        'coa_detail_id',
        'description',
        'debit',
        'kredit',
        'debit_old',
        'kredit_old',
        'debit_new',
        'kredit_new',
        'active',
        'created_by',
        'updated_by'
    ];

    public function general_journal()
    {
        return $this->belongsTo(Tx_lokal_journals::class, 'lokal_journal_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_id', 'id');
    }

    public function coa_detail()
    {
        return $this->belongsTo(Mst_coa_detail::class, 'coa_detail_id', 'id');
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
