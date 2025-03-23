<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkProfileStat extends Model
{
    /** @use HasFactory<\Database\Factories\NetworkProfileStatFactory> */
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'date',
        'opening_balance',
        'closing_balance',
        'current_balance',
        'topup_today',
    ];

    public function profile()
    {
        return $this->belongsTo(NetworkProfile::class, 'profile_id');
    }
}
