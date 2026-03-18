<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mst_menu_user extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_id',
        'user_id',
        'user_access_read',
        'user_access_update',
        'user_access_delete',
        'active',
        'created_by',
        'updated_by'
    ];

    public function menu()
    {
        return $this->belongsTo(Mst_menu::class, 'menu_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
