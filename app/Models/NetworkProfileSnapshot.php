<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkProfileSnapshot extends Model
{
    protected $fillable = ['profile_id', 'balance', 'taken_at'];

    public function profile()
    {
        return $this->belongsTo(NetworkProfile::class, 'profile_id');
    }
}
