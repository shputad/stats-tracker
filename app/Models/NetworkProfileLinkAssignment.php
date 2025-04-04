<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkProfileLinkAssignment extends Model
{
    protected $fillable = [
        'profile_id',
        'link_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $dates = ['assigned_at', 'unassigned_at'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(NetworkProfile::class, 'profile_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class, 'link_id');
    }
}
