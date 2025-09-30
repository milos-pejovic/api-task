<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Log;
use App\Utils\DatabaseUtils;
use App\Utils\GenreUtils;
use App\Services\TMDBApiService;


class MovieService {
    private array $genreMapping;
    private TMDBApiService $tmdbApiService;

    public function __construct() {
        $this->genreMapping = GenreUtils::getGenreMappings();
        $this->tmdbApiService = new TMDBApiService();
    }

    /**
     * Undocumented function
     *
     * @param array|null $moviesApiData
     * @return void
     */
    public function processMovies(?array $moviesApiData, bool $getMovieDetails = false) {
        $moviesTmbdData = $moviesApiData['results'];
        $tmdbIds = array_column($moviesTmbdData, 'id');

        $this->handleBasicData($moviesTmbdData);

        $this->handleGenres($moviesTmbdData);

        if ($getMovieDetails) {
            $this->handleMovieDetails($moviesTmbdData);
        }

        return [
            'status' => 'done',
            'movieTmdbIds' => $tmdbIds
        ];
    }

    /**
     * Undocumented function
     *
     * @param array $moviesTmbdData
     * @return void
     */
    private function handleMovieDetails(array $moviesTmbdData) {
        $moviesToProcess = Movie::whereIn('tmdb_id', array_column($moviesTmbdData, 'id'))
            ->where('has_details', 0)
            ->pluck('tmdb_id')
            ->toArray();

        $dataToInsert = [];
        foreach ($moviesToProcess as $movieTmdbId) {
            $detailsFromApi = $this->tmdbApiService->getSingleMovieDetails($movieTmdbId);

            // If API call failed, skip this record
            if (!$detailsFromApi) {
                continue;
            }

            $dataToInsert[] = [
                'tmdb_id' => $movieTmdbId,
                'budget' => $detailsFromApi['budget'],
                'homepage' => $detailsFromApi['homepage'],
                'origin_country' => implode(',', $detailsFromApi['origin_country']),
                'revenue' => $detailsFromApi['revenue'],
                'tagline' => $detailsFromApi['tagline'],
                'has_details' => 1
            ];
        }

        $tmdbIds = Movie::whereIn('tmdb_id', array_column($dataToInsert, 'tmdb_id'))->pluck('tmdb_id')->toArray();


        //TODO: Research how to move this into DatabaseUtils class
        try {
            Log::info("[DB] Attempting mass upsert of movie records.");
            Movie::whereIn('tmdb_id', array_column($dataToInsert, 'tmdb_id'))
                ->upsert($dataToInsert, ['tmdb_id'], ['budget', 'homepage', 'origin_country', 'revenue', 'tagline']);
            Log::info('[DB] Mass upsert successful');
        } catch (\Exception $e) {
            Log::error("[DB] Mass upsert failed. Attempting one by one. Error: {$e->getMessage()}");
            foreach ($dataToInsert as $dataItem) {
                try {
                    Movie::where('tmdb_id', $dataItem['tmdb_id'])
                        ->update($dataItem);
                } catch (\Exception $e) {
                    Log::error("[DB] Failed updating move record with tmdb_id {$dataItem['tmdb_id']}\nError: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param array $moviesTmbdData
     * @return void
     */
    private function handleGenres(array $moviesTmbdData) {
        $tmdbIds = array_column($moviesTmbdData, 'id');

        // There may be movies we have already inserted into the database
        // But he genre insert failed. We get all the movies that correspond to the 
        // ones from the API data and process the genres.
        // Also, if one or more movies' basic data failed to insert
        // we skip them since the junction table is bound by the
        // foreign key contraints and inserting the movie to genre data may break the query
        $tmdbIdToId = Movie::whereIn('tmdb_id', $tmdbIds)
            ->doesntHave('genres')
            ->pluck('id', 'tmdb_id')
            ->toArray();

        $movieToGenreData = [];
        foreach ($moviesTmbdData as $movieData) {
            if (array_key_exists($movieData['id'], $tmdbIdToId)) {
                foreach ($movieData['genre_ids'] as $tmdb_genre_id) {
                    $movieToGenreData[] = [
                        'movie_id' => $tmdbIdToId[$movieData['id']],
                        'genre_id' => $this->genreMapping[$tmdb_genre_id],
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString()
                    ];
                }
            }
        }

        if (count($movieToGenreData) > 0) {
            DatabaseUtils::insertMassOrOneByOneDB($movieToGenreData, 'genre_movie');
        }
    }

    /**
     * Undocumented function
     *
     * @param array $moviesTmbdData
     * @return void
     */
    private function handleBasicData(array $moviesTmbdData) {
        $tmdbIds = array_column($moviesTmbdData, 'id');

        // Get movies already in the DB
        $existingMoviesTmdbIds = Movie::whereIn('tmdb_id', $tmdbIds)
            ->pluck("tmdb_id")
            ->toArray();

        // Get movies to insert into DB
        $moviesToInsert = array_filter($moviesTmbdData, function($movie) use ($existingMoviesTmdbIds) {
            return !in_array($movie['id'], $existingMoviesTmdbIds);
        });

        // Prepare data for DB insertion
        $dataForDb = [];
        foreach ($moviesToInsert as $movieData) {
            $dataForDb[] = [
                'tmdb_id' => $movieData['id'],
                'title' => $movieData['title'],
                'original_title' => $movieData['original_title'],
                'backdrop_path' => $movieData['backdrop_path'],
                'backdrop_path' => $movieData['backdrop_path'],
                'original_language' => $movieData['original_language'],
                'overview' => $movieData['overview'],
                'poster_path' => $movieData['poster_path'],
                'vote_average' => $movieData['vote_average'],
                'vote_count' => $movieData['vote_count'],
                'release_date' => ($movieData['release_date']) ? $movieData['release_date']  : null,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];
        }

        // Insert data
        $insertedMoviesIds = DatabaseUtils::insertMassOrOneByOne($dataForDb, 'Movie');
    }
}
