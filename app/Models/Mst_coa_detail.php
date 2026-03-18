<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_coa_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'coa_id',
        'branch_id',
        'coa_name',
        'is_tax',
        'active',
        'created_by',
        'updated_by'
    ];

    public function coa()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_id', 'id');
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
