@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <h1>Give me movies</h1>

    <form action="{{ route('search.form.process') }}" method="POST" class="p-4 border rounded shadow-sm bg-light">
        @csrf
        <fieldset>
            <legend class="mb-3">Filter Movies</legend>
  
            <div class="mb-3 col-4">
                <label for="search_name" class="form-label">Search name</label>
                <input type="text" name="search_name" id="search_name" class="form-control">
            </div>

            <hr />

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="details" name="details">
                <label class="form-check-label" for="details">
                Get movie details (slower)
                </label>
            </div>

            <hr />

            <div class="mb-3 col-4">
                <label for="release_from" class="form-label">Released From</label>
                <input type="date" name="release_from" id="release_from" class="form-control">
            </div>

            <hr />

            <div class="mb-3 col-4">
                <label for="release_to" class="form-label">Released To</label>
                <input type="date" name="release_to" id="release_to" class="form-control">
            </div>

            <hr />

            <div class="mb-3">
                <label class="form-label">With genres</label>
                <div class="row">
                    @foreach ($genres as $genre_id => $genre_name)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input type="checkbox" 
                                    name="genres[]" 
                                    value="{{ $genre_id }}" 
                                    id="genre_{{ $genre_id }}" 
                                    class="form-check-input">
                                <label for="genre_{{ $genre_id }}" class="form-check-label">
                                    {{ $genre_name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            <button type="submit" class="btn btn-primary">Search Movies</button>
        </fieldset>
    </form>

@endsection






<!-- 
primary_release_year
int32

primary_release_date.gte
date

primary_release_date.lte
date

release_date.gte
date

release_date.lte
date

sort_by
original_title.asc
original_title.desc
popularity.asc
popularity.desc
revenue.asc
revenue.desc
primary_release_date.asc
title.asc
title.desc
primary_release_date.desc
vote_average.asc
vote_average.desc
vote_count.asc
vote_count.desc

vote_average.gte
float
vote_average.lte
float
vote_count.gte
float
vote_count.lte
float


with_cast
can be a comma (AND) or pipe (OR) separated query

with_crew
can be a comma (AND) or pipe (OR) separated query

with_genres
can be a comma (AND) or pipe (OR) separated query


with_runtime.gte
int32
with_runtime.lte
int32

without_genres

-->
