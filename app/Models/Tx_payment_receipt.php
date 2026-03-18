<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_receipt_no',
        'payment_receipt_plan_no',
        'customer_id',
        'payment_type_id',
        'payment_date',
        'payment_total',
        'payment_total_before_vat',
        'payment_total_after_vat',
        'diskon_pembelian',
        'admin_bank',
        'biaya_kirim',
        'penerimaan_lainnya',
        'payment_mode',
        'coa_id',
        'payment_reference_id',
        'reference_no',
        'reference_date',
        'is_full_payment',
        'remark',
        'pr_created_at',
        'ps_created_at',
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

    public function payment_reference()
    {
        return $this->belongsTo(Mst_global::class, 'payment_reference_id', 'id');
    }

    public function coas()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_id', 'id');
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
