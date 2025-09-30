<?php

namespace App\Utils;

use App\Models\Genre;

class GenreUtils {

    /**
     * getGenreMappings
     *
     * @return array
     */
    public static function getGenreMappings() : array
    {
        return $genres = Genre::all()
            ->pluck('id', 'tmdb_id')
            ->toArray();
    }


    /**
     * getGenreTmdbIdToNameMapping
     *
     * @return array
     */
    public static function getGenreTmdbIdToNameMapping(): array
    {
        return $genres = Genre::all()
            ->pluck('name', 'tmdb_id')
            ->toArray();
    }
}
