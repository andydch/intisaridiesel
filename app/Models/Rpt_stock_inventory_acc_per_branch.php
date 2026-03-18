<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rpt_stock_inventory_acc_per_branch extends Model
{
    use HasFactory;

    protected $table = 'rpt_stock_inventory_acc_per_branchs';
    protected $fillable = [
        'branch_id',
        'rpt_month',
        'rpt_year',
        'purchase_in',
        'sales_out',
        'end_stock',
        'actual_stock',
        'active',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
