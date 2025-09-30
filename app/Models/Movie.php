<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Genre;
use App\Models\Crew;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    protected $guarded = [];

    public function genres() : BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function crew() : BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'crew_movie', 'movie_id', 'crew_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
