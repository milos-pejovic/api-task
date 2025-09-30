<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Movie;

class Crew extends Model
{
    public function movies() : BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'crew_movie', 'crew_id', 'movie_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
