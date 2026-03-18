<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'tax_invoice_no',
        'customer_id',
        'delivery_order_id',
        'invoice_date',
        'invoice_expired_date',
        'tax_invoice_date',
        'branch_id',
        'payment_to_id',
        'do_total',
        'do_vat',
        'do_grandtotal_vat',
        'remark',
        'header',
        'footer',
        'vat_val',
        'approved_by',
        'approved_at',
        'canceled_by',
        'canceled_at',
        'draft_at',
        'draft_to_created_at',
        'is_draft',
        'active',
        'created_by',
        'updated_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function delivery_order()
    {
        return $this->belongsTo(Tx_delivery_order::class, 'delivery_order_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Mst_branch::class, 'branch_id', 'id');
    }

    public function coa()
    {
        return $this->belongsTo(Mst_coa::class, 'payment_to_id', 'id');
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
