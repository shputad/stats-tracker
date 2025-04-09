<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLinkAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'link_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $dates = ['assigned_at', 'unassigned_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(NetworkProfile::class, 'user_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class, 'link_id');
    }
}
