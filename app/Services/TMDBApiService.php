<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 

class TMDBApiService {
    private string $baseUrl = 'https://api.themoviedb.org/3';
    private string $api_key;

    public function __construct(){
        $this->api_key = config('services.tmdb.api_token');
    }

    /**
     * getGenres
     *
     * @return array
     */
    public function getGenres()
    {
        $response = Http::withToken($this->api_key)
            ->acceptJson()
            ->get("{$this->baseUrl}/genre/movie/list");

        if ($response->successful()) {
            $genres = $response->json();
            return $genres;
        } else if ($response->failed()){
            throw new \Exception("Error fetching movie genres. Error:"); //TODO: Add error text
        }
    }

    /**
     * Undocumented function
     *
     * @param integer $genreId
     * @return void
     */
    public function getTopByGenre(array $genreIds) {
        $response = Http::withToken(config('services.tmdb.api_token'))
            ->get("{$this->baseUrl}/discover/movie", [
                // can be a comma (AND) or pipe (OR) separated query TODO: check this
                // 'with_genres' => "12+28", // AND search (genre_1 AND genre_2) DOES NOT ALWAYS WORK
                'with_genres' => implode(",", $genreIds), // OR search (genre_1 OR genre_2)
                'sort_by' => 'vote_average.desc',
                'sort_by' => 'popularity.desc',
                'page' => 1,
                'vote_count.gte' => 1000
            ]);

        if ($response->successful()) {
            $remaining = $response->header('X-RateLimit-Remaining');
            Log::info("Remaining calls: {$remaining}");


            $topMoviesByGenre = $response->json();
            return $topMoviesByGenre;
        } else if ($response->failed()){
            throw new \Exception("Error fetching top movies by genre. Error:"); //TODO: Add error text
        }
    }
}
