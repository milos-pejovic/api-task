<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    public function genres() : BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}


