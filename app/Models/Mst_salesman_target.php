<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_salesman_target extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'branch_id',
        'sales_target',
        'active',
        'created_by',
        'updated_by'
    ];

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
