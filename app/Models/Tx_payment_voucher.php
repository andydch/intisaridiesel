<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_payment_voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_no',
        'payment_voucher_plan_no',
        'supplier_id',
        'payment_type_id',
        'journal_type_id',
        'payment_date',
        'payment_total',
        'payment_total_after_vat',
        'payment_mode',
        'coa_id',
        'payment_reference_id',
        'tagihan_supplier_id',
        'reference_no',
        'reference_date',
        'is_full_payment',
        'remark',
        'admin_bank',
        'biaya_asuransi',
        'biaya_kirim',
        'biaya_lainnya',
        'diskon_pembelian',
        'pv_created_at',
        'ps_created_at',
        'vat_num',
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

    // catatan (9 Okt 2024 11:38:10)
    // - payment_date adalah journal date

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function payment_reference()
    {
        return $this->belongsTo(Mst_global::class, 'payment_reference_id', 'id');
    }

    public function tagihan_supplier()
    {
        return $this->belongsTo(Tx_tagihan_supplier::class, 'tagihan_supplier_id', 'id');
    }

    public function coas()
    {
        return $this->belongsTo(Mst_coa::class, 'coa_id', 'id');
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
