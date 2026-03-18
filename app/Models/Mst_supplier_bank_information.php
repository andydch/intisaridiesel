<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_supplier_bank_information extends Model
{
    use HasFactory;

    protected $table = 'mst_supplier_bank_information';
    protected $fillable = [
        'supplier_id',
        'bank_name',
        'bank_address',
        'account_name',
        'account_no',
        'currency_id',
        'swift_code',
        'bsb_code',
        'active',
        'created_by',
        'updated_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Mst_supplier::class, 'supplier_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Mst_global::class, 'currency_id', 'id');
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
