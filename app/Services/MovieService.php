<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\Crew;
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
            $details = $this->getMovieDetails($moviesTmbdData);
            $this->insertMovieDetails($details);
            $this->handleCast($details);
        }

        return [
            'status' => 'done',
            'movieTmdbIds' => $tmdbIds
        ];
    }

    /**
     * getMovieDetails
     *
     * @param array $moviesTmbdData
     * @return array
     */
    private function getMovieDetails(array $moviesTmbdData) : array
    {
        $moviesToProcess = Movie::whereIn('tmdb_id', array_column($moviesTmbdData, 'id'))
            ->where('has_details', 0)
            ->pluck('tmdb_id')
            ->toArray();

        $details = [];
        foreach ($moviesToProcess as $movieTmdbId) {
            $data = $this->tmdbApiService->getSingleMovieDetails($movieTmdbId);
            if (!$data) {
                continue;
            }
            $details[$movieTmdbId] = $data;
        }
        return $details;
    }

    /**
     * insertMovieDetails
     *
     * @param array $details
     * @return void
     */
    private function insertMovieDetails(array $details) {
        $dataToInsert = [];
        foreach ($details as $tmdb_id => $movieDetails) {
            $dataToInsert[] = [
                'tmdb_id' => $tmdb_id,
                'status' => $movieDetails['status'],
                'budget' => $movieDetails['budget'],
                'homepage' => $movieDetails['homepage'],
                'origin_country' => implode(',', $movieDetails['origin_country']),
                'revenue' => $movieDetails['revenue'],
                'tagline' => $movieDetails['tagline'],
                'has_details' => 1
            ];
        }

        DatabaseUtils::bulkUpsertMoviesOrOneByOne($dataToInsert);
    }

    /**
     * handleMovieDetails
     *
     * @param array $moviesTmbdData
     * @return void
     */
    private function handleCast(array $details) {

        // Insert cast
        $castDataToInsert = [];
        foreach ($details as $movieTmdbData) {
            //TODO: For now we process only cast, not crew (director, art direcetor, ...)
            $cast = $movieTmdbData['credits']['cast'];
            $castTmdbIds = array_column($cast, 'id');
            $existingCastTmdbIds = Crew::whereIn('tmdb_id', $castTmdbIds)
                ->pluck('tmdb_id')
                ->toArray();

            $castToProcess = array_filter($cast, function($castMember) use ($existingCastTmdbIds) {
                return !in_array($castMember['id'], $existingCastTmdbIds);
            });

            foreach ($castToProcess as $castMemberData) {
                // We deliberately set tmdb_id as the array key so that if multiple movies would add 
                // the same actor the new data overwrites the old. We remove duplicates this way.
                $castDataToInsert[$castMemberData['id']] = [
                    'tmdb_id' => $castMemberData['id'],
                    'gender' => $castMemberData['gender'],
                    'name' => $castMemberData['name'],
                    'profile_path' => $castMemberData['profile_path']
                ];
            }
        }
        DatabaseUtils::insertMassOrOneByOne($castDataToInsert, 'Crew');

        // Handle movies to cast relationship

        // Get tmdb_id of all the cast from the movie details
        $castTmbdIds = array_reduce($details, function($allCastTmdbIds, $movieData) {
            $allCastTmdbIds = array_merge($allCastTmdbIds, array_column($movieData['credits']['cast'], 'id'));
            return $allCastTmdbIds;
        }, []);
        $castTmbdIds = array_unique($castTmbdIds);

        $castTmbdIdToId = Crew::whereIn('tmdb_id', $castTmbdIds)
            ->pluck('id', 'tmdb_id')
            ->toArray();

        $moviesTmdbToId = Movie::whereIn('tmdb_id', array_column($details, 'id'))
            ->pluck('id', 'tmdb_id')
            ->toArray();
    
        $movieToCastRelationship = [];

        foreach($details as $movieTmdbData) {
            foreach($movieTmdbData['credits']['cast'] as $castData) {
                if (array_key_exists($castData['id'], $castTmbdIdToId)) {
                    $movieToCastRelationship[] = [
                        'movie_id' => $moviesTmdbToId[$movieTmdbData['id']],
                        'crew_id' => $castTmbdIdToId[$castData['id']],
                        'role' => $castData['character'],
                    ];
                } else {
                    Log::error("[DB] Crew tmdb_id: {$castData['id']}");
                }
            }
        }

        DatabaseUtils::insertMassOrOneByOneDB($movieToCastRelationship, 'crew_movie');
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
                'popularity' => $movieData['popularity'],
                'release_date' => ($movieData['release_date']) ? $movieData['release_date']  : null,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];
        }

        // Insert data
        $insertedMoviesIds = DatabaseUtils::insertMassOrOneByOne($dataForDb, 'Movie');
    }
}
