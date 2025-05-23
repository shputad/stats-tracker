<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class NetworkProfile extends Model
{
    /** @use HasFactory<\Database\Factories\NetworkProfileFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'channel_id', 'account_id', 'api_username', 'api_password', 'api_key', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function networkChannel()
    {
        return $this->belongsTo(NetworkChannel::class, 'channel_id');
    }

    public function stats()
    {
        return $this->hasMany(NetworkProfileStat::class, 'profile_id');
    }

    public function snapshots()
    {
        return $this->hasMany(NetworkProfileSnapshot::class, 'profile_id');
    }

    public function setApiUsernameAttribute($value)
    {
        $this->attributes['api_username'] = Crypt::encryptString($value);
    }

    public function getApiUsernameAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setApiPasswordAttribute($value)
    {
        $this->attributes['api_password'] = Crypt::encryptString($value);
    }

    public function getApiPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
