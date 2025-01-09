<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkProfile extends Model
{
    /** @use HasFactory<\Database\Factories\NetworkProfileFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'channel_id', 'link_id', 'account_id', 'api_key', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function networkChannel()
    {
        return $this->belongsTo(NetworkChannel::class, 'channel_id');
    }

    public function link()
    {
        return $this->belongsTo(Link::class, 'link_id');
    }

    public function stats()
    {
        return $this->hasMany(NetworkProfileStat::class, 'profile_id');
    }
}
