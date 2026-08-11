<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookSession extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function sessionPackage()
    {
        return $this->belongsTo(SessionPackage::class, 'session_package_id');
    }

    public function sessionUsage()
    {
        return $this->hasOne(SessionUsage::class);
    }
}
