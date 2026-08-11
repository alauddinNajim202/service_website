<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $fillable = ['user_id', 'favourite_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favouriteUser()
    {
        return $this->belongsTo(User::class, 'favourite_user_id');
    }
}
