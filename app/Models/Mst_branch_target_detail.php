<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_branch_target_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_target_id',
        'branch_id',
        'year_per_branch',
        'sales_target_per_branch',
        'active',
        'created_by',
        'updated_by',
    ];

    public function branch_target()
    {
        return $this->belongsTo(Mst_branch_target::class, 'branch_target_id', 'id');
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
