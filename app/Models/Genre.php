<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    /**
     * makeMultipleFromApiData
     * 
     * To be called from the seeder.
     *
     * @param [type] $apiGenreData
     * @return void
     */
    public static function makeMultipleFromApiData($apiGenreData) {
        $genresData = $apiGenreData["genres"];

        $dataForInsertion = [];
        foreach ($genresData as $genreData) {
            $dataForInsertion[] = [
                'tmdb_id' => $genreData['id'],
                'name' => $genreData['name'],
            ];
        }
        self::upsert($dataForInsertion, ["tmdb_id"], ["name"]);
    }

    public function movies() : BelongsToMany
    {
        return $this->belongsToMany(Movie::class);
    }
}
