<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory;

    protected $fillable = ['name', 'build_tag', 'url', 'type', 'status', 'api_url', 'base_logs_type'];

    public function linkStats()
    {
        return $this->hasMany(LinkStat::class);
    }
}
