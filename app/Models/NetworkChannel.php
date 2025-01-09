<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkChannel extends Model
{
    /** @use HasFactory<\Database\Factories\NetworkChannelFactory> */
    use HasFactory;

    protected $fillable = ['name', 'website', 'has_api', 'status'];

    public function links()
    {
        return $this->hasMany(Link::class);
    }
}
