<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Search;
use App\Services\TMDBApiService;
use App\Models\Movie;
use App\Services\MovieService;

class TMDBService {

    private TMDBApiService $tmdbApi;
    private MovieService $movieService;

    public function __construct(){
        $this->tmdbApi = new TMDBApiService();
        $this->movieService = new MovieService();
    }

    /**
     * Undocumented function
     *
     * @param integer $searchId
     * @return void
     */
    public function getMovies(int $searchId) {
        Log::info('Searching for movies');
        $search = Search::find($searchId);
        $params = json_decode($search->search_parameters, true);
        if (!$params) {
            throw new \Exception("No search parameters found, aborting");
        }
        $getMovieDetails = isset($params["get_details"]) ? true : false;
        $prepared_params = $this->prepareParametersForDiscover($params);

        $moviesFromTmdb = $this->tmdbApi->discover($prepared_params);

        $result = $this->movieService->processMovies($moviesFromTmdb, $getMovieDetails);
        $ids = $result['movieTmdbIds'];

        $search->movies_tmdb_ids = implode(',', $ids);
        $search->status = $result['status'];
        $search->save();
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return array
     */
    private function prepareParametersForDiscover(array $params) : array
    {
        Log::info($params);

        // Genres
        $prepared_params = [];
        if (key_exists('genres', $params)) {
            $prepared_params['with_genres'] = implode(',', $params['genres']);
        }
        //TODO: Add Genres "OR" search
        //TODO: Add Without genres

        // Realease date - start
        if (key_exists('release_from', $params) and $params['release_from']) {
            $prepared_params['primary_release_date.gte'] = $params['release_from'];
        }

        // Realease date - end
        if (key_exists('release_to', $params) and $params['release_to']) {
            $prepared_params['primary_release_date.lte'] = $params['release_to'];
        }

        // Sorting
        if (
            key_exists('sort_by', $params) and 
            $params['sort_by'] and
            key_exists('sort_order', $params) and 
            $params['sort_order']
        ) {
            $prepared_params['sort_by'] = $params['sort_by'] . "." . $params['sort_order'];
        }

        // Vote average
        if (
            key_exists('vote_average', $params) and 
            $params['vote_average'] != '-' and
            key_exists('vote_average_direction', $params) and 
            $params['vote_average_direction']
        ) {
            if ($params['vote_average_direction'] == 'lte') {
                $prepared_params['vote_average.lte'] =  $params['vote_average'];
            } else if ($params['vote_average_direction'] == 'gte') {
                $prepared_params['vote_average.gte'] =  $params['vote_average'];
            } else {
                Log::error("There was an error setting vote average.");
            }
        }

        // Vote count
         if (
            key_exists('vote_count', $params) and 
            $params['vote_count'] != '-' and
            key_exists('vote_count_direction', $params) and 
            $params['vote_count_direction']
        ) {
            if ($params['vote_count_direction'] == 'lte') {
                $prepared_params['vote_count.lte'] =  $params['vote_count'];
            } else if ($params['vote_count_direction'] == 'gte') {
                $prepared_params['vote_count.gte'] =  $params['vote_count'];
            } else {
                Log::error("There was an error setting vote average.");
            }
        }

        Log::info($prepared_params);
        $prepared_params['adult'] = false; // We do not process adult movies.

        return $prepared_params;
    }
}
