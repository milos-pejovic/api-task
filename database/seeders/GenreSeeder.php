<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Services\TMDBApiService;
use App\Models\Genre;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(TMDBApiService $tmdb): void
    {
        $genres = $tmdb->getGenres();
        Genre::makeMultipleFromApiData($genres);
    }
}
