<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_acceptance_plan_per_invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'acceptance_plan_id',
        'plan_date',
        'plan_accept',
        'inv_or_kwi_id',
        'inv_or_kwi',
        'customer_id',
        'invoice_no',
        'active',
        'created_by',
        'updated_by'
    ];

    public function acceptancePlan()
    {
        return $this->belongsTo(Tx_acceptance_plan::class, 'acceptance_plan_id', 'id');
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
