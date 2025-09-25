<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TMDBApiService;
use Illuminate\Support\Facades\Log; 
use App\Models\Search;
use App\Jobs\ProcessMovieData;

class MoviesController extends Controller
{
    /**
     * Genre IDs in The Movie Database
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
        return view('movies.form')->with([
            "genres" => $this->genres
        ]);
    }

    /**
     * processForm
     *
     * @param Request $request
     * @return void
     */
    public function processForm(Request $request){
        //TODO Move this to a custom form validation class
        $validated = $request->validate([
            'search_name'  => 'required|string|max:255',
            'release_from'  => 'nullable|date',
            'release_to'    => 'nullable|date|after_or_equal:release_from',
            'genres'         => 'nullable|array',
            'genres.*'       => 'integer',
        ]);
        //TODO Move this to a custom form validation class

        $search = Search::create($validated);
        ProcessMovieData::dispatch($search );
        return redirect()->route('movies.form.show');
    }
}
