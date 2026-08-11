<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorSessionPrice extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function sessionPackage()
    {
        return $this->belongsTo(SessionPackage::class, 'session_package_id');
    }
}
