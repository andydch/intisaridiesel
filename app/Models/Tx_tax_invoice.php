<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_tax_invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'fp_no',
        'prefiks_code',
        'active',
        'updated_by',
        'created_by'
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
