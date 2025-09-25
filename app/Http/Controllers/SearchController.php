<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search;

class SearchController extends Controller
{
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
}
