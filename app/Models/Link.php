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

    // Mutator: encode before saving to DB
    public function setUrlAttribute($value)
    {
        $this->attributes['url'] = base64_encode($value);
    }

    // Accessor: decode when retrieving from DB
    public function getUrlAttribute($value)
    {
        return base64_decode($value);
    }

    public function setApiUrlAttribute($value)
    {
        $this->attributes['api_url'] = $value ? base64_encode($value) : null;
    }

    public function getApiUrlAttribute($value)
    {
        return $value ? base64_decode($value) : null;
    }
}
