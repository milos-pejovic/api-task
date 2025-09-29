<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Utils\DatabaseUtils;
use App\Utils\GenreUtils;


class MovieService {
    private array $genreMapping;
    private array $status = [];

    public function __construct() {
        $this->genreMapping = GenreUtils::getGenreMappings();
    }

    /**
     * Undocumented function
     *
     * @param array|null $moviesApiData
     * @return void
     */
    public function processMovies(?array $moviesApiData, bool $getMovieDetails) {
        $moviesTmbdData = $moviesApiData['results'];
        $tmdbIds = array_column($moviesTmbdData, 'id');

        $this->handleBasicData($moviesTmbdData);

        $this->handleGenres($moviesTmbdData);

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


















// class _MovieService {

//     /**
//      * getExistingMoviesTmdbIds
//      *
//      * @param array $movieTmdbIds
//      * @return array
//      */
//     private function getExistingMoviesTmdbIds(array $movieTmdbIds) : array
//     {
//         return Movie::whereIn("tmdb_id", $movieTmdbIds)
//             ->pluck("tmdb_id")
//             ->toArray();
//     }

//     /**
//      * getMoviesToInsert
//      *
//      * @param array $moviesFromTmdb
//      * @param array $existingMoviesTmdbsIds
//      * @return array
//      */
//     private function getMoviesToInsert(array $moviesFromTmdb, array $existingMoviesTmdbsIds) : array
//     {
//         return array_filter($moviesFromTmdb, function($movie) use ($existingMoviesTmdbsIds) {
//             return !in_array($movie['id'], $existingMoviesTmdbsIds);
//         });
//     }

//     /**
//      * insertMovies
//      *
//      * @param array $moviesToInsert
//      * @return array
//      */
//     private function insertMovies(array $moviesToInsert) : array
//     {
//         Log::info('Preparing movie data for local DB');
//         $genresPerMovieTmbdIds = [];
//         $newMoviesData = [];
//         foreach ($moviesToInsert as $movie) {
//             $movie['tmdb_id'] = $movie['id'];
//             $genresPerMovieTmbdIds[$movie['tmdb_id']] = $movie['genre_ids'];
//             unset($movie['id']);
//             unset($movie['genre_ids']);
//             unset($movie['adult']);
//             unset($movie['video']); //TODO: possibly keep?
//             unset($movie['backdrop_path']); //TODO: possibly keep?
//             $movie['release_date'] = ($movie['release_date']) ? $movie['release_date']  : null;
//             $movie['created_at'] = now()->toDateTimeString();
//             $movie['updated_at'] = now()->toDateTimeString();
//             $newMoviesData[] = $movie;
//         }

//         Log::info('Before entering movie data into local DB');

//         DatabaseUtils::insertMassOrOneByOne($newMoviesData, "Movie");

//         Log::info('After entering movie data into local DB');
//         return $genresPerMovieTmbdIds;
//     }

//     /**
//      * getExistingMovieTmbdIdsToIds
//      *
//      * @param array $moviesToInsert
//      * @return array
//      */
//     private function getExistingMovieTmbdIdsToIds(array $moviesToInsert): array
//     {
//         $existingMovieRecords = Movie::whereIn("tmdb_id", array_column($moviesToInsert, 'id'))
//             ->get(['id', 'tmdb_id'])
//             ->toArray();
            
//         return array_combine(
//             array_column($existingMovieRecords, 'tmdb_id'),
//             array_column($existingMovieRecords, 'id')
//         );
//     }

//     /**
//      * insertGenreData
//      *
//      * @param array $genresPerMovieTmbdIds
//      * @param array $movieTmdbIdsToIds
//      * @param array $genreTmdbsToIds
//      * @return void
//      */
//     private function insertGenreData(
//         array $genresPerMovieTmbdIds, 
//         array $movieTmdbIdsToIds, 
//         array $genreTmdbsToIds
//     ) {
//         Log::info('Preparing movie to genre data');
//         $genresDataToAdd = [];
//         foreach ($genresPerMovieTmbdIds as $movieTmdbId => $genreTmdbIds) {
//             foreach ($genreTmdbIds as $genreTmbdId) {
//                 $genresDataToAdd[] = [
//                     'movie_id'   => $movieTmdbIdsToIds[$movieTmdbId],
//                     'genre_id'   => $genreTmdbsToIds[$genreTmbdId],
//                     'created_at' => now()->toDateTimeString(),
//                     'updated_at' => now()->toDateTimeString()
//                 ];
//             }
//         }

//         Log::info('Before entering movie to genre data');
//         if ($genresDataToAdd) {
//             Log::info("Entering genre data into DB.");
//             DB::table('genre_movie')->insertOrIgnore($genresDataToAdd);
//         }
//         Log::info('After entering movie to genre data');
//     }

//     /**
//      * processMovies
//      *
//      * @param array $moviesData
//      * @param boolean $getMovieDetails
//      * @return array
//      */
//     public function processMovies(?array $moviesData, bool $getMovieDetails) : array
//     {
//         $genreTmdbsToIds = GenreUtils::getGenreMappings();
//         $moviesFromTmdb = $moviesData['results'];
//         $movieTmdbIds = array_column($moviesFromTmdb, 'id');

//         // Get the tmdb_id of movies already in the local DB
//         $existingMoviesTmdbsIds = $this->getExistingMoviesTmdbIds($movieTmdbIds);

//         // Get the movies to be inserted into local DB
//         $moviesToInsert = $this->getMoviesToInsert($moviesFromTmdb, $existingMoviesTmdbsIds);

//         if (count($moviesToInsert) == 0) {
//             Log::info("All movies from the TMBD API are already in the local database.");
//             return ['movieTmdbIds' => $movieTmdbIds, 'status' => 'done'];
//         }

//         try {
//             DB::beginTransaction();

//             // Insert movies into local DB
//             // Get the genres per movie mappings
//             $genresPerMovieTmbdIds = $this->insertMovies($moviesToInsert);

//             // Get tmdb_id to id mappings of records entered into database
//             $movieTmdbIdsToIds = $this->getExistingMovieTmbdIdsToIds($moviesToInsert);

//             // Insert movie to genre data
//             $this->insertGenreData($genresPerMovieTmbdIds, $movieTmdbIdsToIds, $genreTmdbsToIds);

//             DB::commit();
//         } catch(\Exception $e) {
//             Log::error("ERROR entering data into local database. Error: {$e->getMessage()}");
//             DB::rollback();
//             throw $e;
//         }

//         return [
//             'movieTmdbIds' => $movieTmdbIds,
//             'status' => 'done'
//         ];
//     }
// }
