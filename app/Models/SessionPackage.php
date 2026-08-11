<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionPackage extends Model
{
    protected $guarded = [];

    protected $appends = ['featured', 'featuredText'];

    public function bookSessions()
    {
        return $this->hasMany(BookSession::class);
    }

    public function getFeaturedAttribute(): bool
    {
        return (bool)$this->is_feature;
    }

    public function getFeaturedTextAttribute(): ?string
    {
        return $this->feature_text;
    }
}
