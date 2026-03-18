<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_activity_user extends Model
{
    use HasFactory;

    // protected $table = 'log_activity_users';

    protected $fillable = [
        'user_id',
        'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
