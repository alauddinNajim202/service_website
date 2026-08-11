<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionUsage extends Model
{
    protected $guarded = [];

    public function bookSession()
    {
        return $this->belongsTo(BookSession::class);
    }
}
