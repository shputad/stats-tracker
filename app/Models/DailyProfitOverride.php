<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyProfitOverride extends Model
{
    protected $fillable = ['link_id', 'date', 'override_cr'];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
