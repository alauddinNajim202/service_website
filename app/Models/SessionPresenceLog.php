<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionPresenceLog extends Model
{
    protected $guarded = [];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bookSession()
    {
        return $this->belongsTo(BookSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
