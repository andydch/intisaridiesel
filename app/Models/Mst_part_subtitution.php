<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_part_subtitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'part_other_id',
        'active',
        'created_by',
        'updated_by',
    ];

    public function part()
    {
        return $this->belongsTo(Mst_part::class, 'part_id', 'id');
    }

    public function part_other()
    {
        return $this->belongsTo(Mst_part::class, 'part_other_id', 'id');
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
