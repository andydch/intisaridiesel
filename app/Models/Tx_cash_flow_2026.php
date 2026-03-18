<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tx_cash_flow_2026 extends Model
{
    use HasFactory;

    protected $table = 'tx_cash_flows_2026';
    protected $fillable = [
        'report_code',
        'row_number',
        'col_number',
        'period',
        'bank_id',
        'cell_values',
        'f_color',
        'b_color',
        'font_size',
        'font_weight',
        'font_style',
        'text_align',
        'created_by',
    ];

    public function bank()
    {
        return $this->belongsTo(Mst_coa::class, 'bank_id', 'id');
    }
}
