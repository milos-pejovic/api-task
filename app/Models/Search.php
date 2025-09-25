<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    //TODO: add fillable, guarded etc

    /**
     * Undocumented function
     *
     * @param array $search_parameters
     * @return void
     */
    public static function create(array $search_parameters){
        $search = new self();
        $search->name = $search_parameters['search_name'];
        $search->search_parameters = json_encode($search_parameters);
        $search->save();
    }
}
