<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    /**
     * create
     *
     * @param array $search_parameters
     * @return int
     */
    public static function create(array $search_parameters){
        $search = new self();
        $search->name = $search_parameters['search_name'];
        $search->search_parameters = json_encode($search_parameters);
        $search->save();
        return $search->id;
    }
}
