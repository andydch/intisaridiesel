<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_stock_transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_no',
        'stock_transfer_date',
        'branch_from_id',
        'branch_to_id',
        'remark',
        'draft_at',
        'draft_to_created_at',
        'is_draft',
        'active',
        'approved_by',
        'approved_at',
        'canceled_by',
        'canceled_at',
        'received_by',
        'received_at',
        'updated_by',
        'created_by'
    ];

    public function branch_from()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_from_id', 'id');
    }

    public function branch_to()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_to_id', 'id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by', 'id');
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
