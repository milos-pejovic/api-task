<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search;
use App\Jobs\ProcessMovieDataJob;
use App\Models\Movie;

class SearchController extends Controller
{
    /**
     * Genre IDs in The Movie Database
     * //TODO Move this to a utility class
     *
     * @var array
     */
    private array $genres = [
        28    => "Action",
        12    => "Adventure",
        16    => "Animation",
        35    => "Comedy",
        80    => "Crime",
        99    => "Documentary",
        18    => "Drama",
        10751 => "Family",
        14    => "Fantasy",
        36    => "History",
        27    => "Horror",
        10402 => "Music",
        9648  => "Mystery",
        10749 => "Romance",
        878   => "Science Fiction",
        10770 => "TV Movie",
        53    => "Thriller",
        10752 => "War",
        37    => "Western",
    ];

    /**
     * showForm
     *
     * @param Request $request
     * @return Illuminate\View\View
     */
    public function showForm(){
        return view('searches.form')->with([
            "genres" => $this->genres
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
            'search_name'   => 'required|string|max:255',
            'release_from'  => 'nullable|date',
            'release_to'    => 'nullable|date|after_or_equal:release_from',
            'genres'        => 'nullable|array',
            'genres.*'      => 'integer',
            'details'       => 'nullable'
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
        //TODO: move this somewhere else?
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

        // dd($genreMapping );

        $search = Search::latest()->first();
        $tmdb = new \App\Services\TMDBService();
        $tmdb->getMovies($search->id);
    }
    //TODO: remove
}
