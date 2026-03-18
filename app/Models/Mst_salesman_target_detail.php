<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_salesman_target_detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesman_target_id',
        'salesman_id',
        'year_per_branch',
        'sales_target_per_branch',
        'active',
        'created_by',
        'updated_by'
    ];

    public function salesman_target()
    {
        return $this->belongsTo(Mst_salesman_target::class, 'salesman_target_id', 'id');
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id', 'id');
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
