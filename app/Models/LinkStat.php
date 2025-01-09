<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkStat extends Model
{
    /** @use HasFactory<\Database\Factories\LinkStatFactory> */
    use HasFactory;

    protected $fillable = ['link_id', 'log'];

    public function link()
    {
        return $this->belongsTo(Link::class, 'link_id');
    }

    /**
     * Scope to filter stats from the last 10 minutes.
     */
    public function scopeLast10Minutes($query)
    {
        return $query->where('created_at', '>=', now()->subMinutes(10));
    }

    /**
     * Scope to filter stats from the last hour (complete hour).
     */
    public function scopeLastHour($query)
    {
        $startOfLastHour = now()->startOfHour()->subHour();
        $endOfLastHour = now()->startOfHour();

        return $query->whereBetween('created_at', [$startOfLastHour, $endOfLastHour]);
    }

    /**
     * Scope to filter stats for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }
}
