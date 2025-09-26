<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;;


class MovieService {


    private function getGenreMappings() {
        $genres = \App\Models\Genre::all()->toArray();
        return array_combine(
            array_column($genres, "tmdb_id"), 
            array_column($genres, "id")
        );
    }

    private function getExistingMoviesTmdbIds($movieTmdbIds) {
        return Movie::whereIn("tmdb_id", $movieTmdbIds)
            ->pluck("tmdb_id")
            ->toArray();
    }

    private function getMoviesToInsert($moviesFromTmdb, $existingMoviesTmdbsIds) {
        return array_filter($moviesFromTmdb, function($movie) use ($existingMoviesTmdbsIds) {
            return !in_array($movie['id'], $existingMoviesTmdbsIds);
        });
    }

    /**
     * Undocumented function
     *
     * @param [type] $moviesData
     * @return array
     */
    public function insertMoviesFromApi($moviesData) : array
    {
        $genreTmdbsToIds = $this->getGenreMappings();

        Log::info('111');

        $moviesFromTmdb = $moviesData['results'];
        $movieTmdbIds = array_column($moviesFromTmdb, 'id');
        $existingMoviesTmdbsIds = $this->getExistingMoviesTmdbIds($movieTmdbIds);
        $moviesToInsert = $this->getMoviesToInsert($moviesFromTmdb, $existingMoviesTmdbsIds);

        if (count($moviesToInsert) == 0) {
            Log::info("No new movies to insert.");
            return $movieTmdbIds;
        }

        $newMoviesData = [];
        foreach ($moviesToInsert as $movie) {
            $movie['tmdb_id'] = $movie['id'];
            $genresPerMovieTmbdIds[$movie['tmdb_id']] = $movie['genre_ids'];
            unset($movie['id']);
            unset($movie['genre_ids']);
            unset($movie['adult']);
            unset($movie['video']);
            unset($movie['backdrop_path']);
            $movie['release_date'] = ($movie['release_date']) ? $movie['release_date']  : null;
            $movie['created_at'] = now()->toDateTimeString();
            $movie['updated_at'] = now()->toDateTimeString();
            $newMoviesData[] = $movie;
        }

        Log::info(222);

        Movie::insert($newMoviesData);

        Log::info(333);
        
        $existingMovieRecords = Movie::whereIn("tmdb_id", array_column($moviesToInsert, 'id'))
            ->get(['id', 'tmdb_id'])
            ->toArray();
            
        $movieTmdbIdsToIds = array_combine(
            array_column($existingMovieRecords, 'tmdb_id'),
            array_column($existingMovieRecords, 'id')
        );

        Log::info(444);

        $genresDataToAdd = [];

        foreach ($genresPerMovieTmbdIds as $movieTmdbId => $genreTmdbIds) {
            foreach ($genreTmdbIds as $genreTmbdId) {
                $genresDataToAdd[] = [
                    'movie_id'   => $movieTmdbIdsToIds[$movieTmdbId],
                    'genre_id'   => $genreTmdbsToIds[$genreTmbdId],
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString()
                ];
            }
        }

        Log::info(555);

        if ($genresDataToAdd) {
            DB::table('genre_movie')->insertOrIgnore($genresDataToAdd);
        }

        return $movieTmdbIds;
    }
}
