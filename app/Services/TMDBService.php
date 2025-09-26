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
        $prepared_params = $this->prepareParametersForDiscover($params);

        Log::info($params);

        $moviesFromTmdb = $this->tmdbApi->discover($prepared_params);
        $ids = $this->movieService->insertMoviesFromApi($moviesFromTmdb);

            // TODO
            // If the User checked the "detailed movie data"
            // Check which movies have teh detailed data (have a bool field "detailed_data")
            // Get details for every movie without detailed data 

        $search->movies_tmdb_ids = implode(',', $ids);
        $search->status = 'done';
        $search->save();

        Log::info('SUCCESS');
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return array
     */
    private function prepareParametersForDiscover(array $params) : array
    {
        $prepared_params = [];
        if (key_exists('genres', $params)) {
            $prepared_params['with_genres'] = implode(',', $params['genres']);
        }
        //TODO: Add Genres "OR" search

        if (key_exists('release_from', $params) and $params['release_from']) {
            $prepared_params['primary_release_date.gte'] = $params['release_from'];
        }

        if (key_exists('release_to', $params) and $params['release_to']) {
            $prepared_params['primary_release_date.lte'] = $params['release_to'];
        }

        $prepared_params['sort_by'] = 'primary_release_date.desc'; //TODO: remove?
        $prepared_params['adult'] = false; 

        return $prepared_params;
    }
}
