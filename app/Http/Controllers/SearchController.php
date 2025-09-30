<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search;
use App\Jobs\ProcessMovieDataJob;
use App\Models\Movie;
use App\Utils\GenreUtils;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    private array $sortBy = ['original_title', 'popularity', 'revenue', 'primary_release_date', 'title', 'vote_average', 'vote_count'];

    /**
     * showForm
     *
     * @param Request $request
     * @return Illuminate\View\View
     */
    public function showForm(){
        $genres = GenreUtils::getGenreTmdbIdToNameMapping();
        return view('searches.form')->with([
            'genres' => $genres,
            'sortBy' => $this->sortBy
        ]);
    }

    /**
     * processForm
     *
     * @param Request $request
     * @return void //TODO: return redirect
     */
    public function processForm(Request $request){
        $validated = $request->validate([
            'search_name'               => 'required|string|max:255',
            'release_from'              => 'nullable|date',
            'release_to'                => 'nullable|date|after_or_equal:release_from',
            'genres'                    => 'nullable|array',
            'genres.*'                  => 'integer',
            'details'                   => 'nullable',
            'sort_by'                   => Rule::in($this->sortBy),
            'sort_order'                => Rule::in(['asc', 'desc']),
            'vote_average'              => 'nullable', Rule::in(['-', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'vote_average_direction'    => Rule::in(['lte', 'gte']),
            'vote_count'                => 'nullable|integer',
            'vote_count_direction'      => Rule::in(['lte', 'gte'])
        ]);

        $search_id = Search::create($validated);
        ProcessMovieDataJob::dispatch($search_id);
        return redirect()->route('search.list');
    }

    /**
     * list
     *
     * @return void
     */
    public function list(){
        $searches = Search::all();
        return view('searches.list')->with([
            'searches' => $searches
        ]);
    }

    /**
     * report
     *
     * @param integer $searchId
     * @return void
     */
    public function report(int $searchId){
        $search = Search::find($searchId);
        $movieTmdbIds = explode(',', $search->movies_tmdb_ids);
        $movies = Movie::whereIn('tmdb_id', $movieTmdbIds)->with('genres')->get();

        return view('searches.report')->with([
            'movies' => $movies
        ]);
    }

    //TODO: remove
    public function test() {
        // $genres = \App\Models\Genre::all()->toArray();
        // $genreMapping = array_combine(array_column($genres, "tmdb_id"), array_column($genres, "id"));

        $search = Search::latest()->first();
        $tmdb = new \App\Services\TMDBService();
        $tmdb->getMovies($search->id);
    }
    //TODO: remove
}
