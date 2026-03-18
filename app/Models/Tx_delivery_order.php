<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_delivery_order extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_no',
        'delivery_order_date',
        'faktur_expired_date',
        'sales_order_no_all',
        'tax_invoice_id',
        'customer_id',
        'customer_entity_type_id',
        'customer_name',
        'c_shipment_addr_id',
        'courier_id',
        'remark',
        'branch_id',
        'total_qty',
        'total_before_vat',
        'total_after_vat',
        'is_draft',
        'draft_at',
        'draft_to_created_at',
        'is_vat',
        'vat_val',
        'number_of_prints',
        'faktur_dl_date',
        'active',
        'created_by',
        'updated_by',
        'adjustment_by',
        'adjustment_at',
    ];

    public function tax_invoice()
    {
        return $this->belongsTo(Tx_tax_invoice::class, 'tax_invoice_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Mst_customer::class, 'customer_id', 'id');
    }

    public function customer_shipment_address()
    {
        return $this->belongsTo(Mst_customer_shipment_address::class, 'c_shipment_addr_id', 'id');
    }

    public function courier()
    {
        return $this->belongsTo(Mst_courier::class, 'courier_id', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function adjustmentBy()
    {
        return $this->belongsTo(User::class, 'adjustment_by', 'id');
    }
}
