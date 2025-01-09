<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkProfileStat extends Model
{
    /** @use HasFactory<\Database\Factories\NetworkProfileStatFactory> */
    use HasFactory;

    protected $fillable = ['profile_id', 'topups', 'budget'];

    public function profile()
    {
        return $this->belongsTo(NetworkProfile::class, 'profile_id');
    }
}
