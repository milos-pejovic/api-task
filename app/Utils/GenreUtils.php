<?php

namespace App\Utils;

use App\Models\Genre;

class GenreUtils {

    /**
     * Undocumented function
     *
     * @return array
     */
    public static function getGenreMappings() : array
    {
        $genres = Genre::all()->toArray();
        return array_combine(
            array_column($genres, "tmdb_id"), 
            array_column($genres, "id")
        );
    }
}
