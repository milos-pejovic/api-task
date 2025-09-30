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

            <!-- Get details -->
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="get_details" name="get_details" checked>
                <label class="form-check-label" for="get_details">
                Get movie details (slower)
                </label>
            </div>

            <hr />

            <!-- Release year -->
            <div class="row mb-3">
                <div class="col-3">
                    <label for="release_from" class="form-label">Released From</label>
                    <input type="date" name="release_from" id="release_from" class="form-control">
                </div>

                <div class="col-3">
                    <label for="release_to" class="form-label">Released To</label>
                    <input type="date" name="release_to" id="release_to" class="form-control">
                </div>
            </div>

            <hr />

            <!-- Sorting -->
            <div class="row mb-3 align-items-center">
                <!-- Dropdown -->
                <div class="col-auto">
                    <label for="sort_by" class="form-label me-2">Sort By</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="original_title">Original Title</option>
                        <option value="popularity">Popularity</option>
                        <option value="revenue">Revenue</option>
                        <option value="primary_release_date">Primary Release Date</option>
                        <option value="title">Title</option>
                        <option value="vote_average" selected="selected">Vote Average</option>
                        <option value="vote_count">Vote Count</option>
                    </select>
                </div>

                <!-- Radio buttons -->
                <div class="col-auto">
                    <label for="sort_order" class="form-label me-2">Sort direction</label><br />
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="sort_order" id="asc" value="asc">
                        <label class="form-check-label" for="asc">Ascending</label>
                    </div>
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="sort_order" id="desc" value="desc" checked>
                        <label class="form-check-label" for="desc">Descending</label>
                    </div>
                </div>
            </div>

            <hr />

            <!-- Vote average -->
            <div class="row align-items-center">
                <!-- Dropdown -->
                <div class="col-2">
                    <label for="vote_average" class="form-label me-2">Vote average</label>
                    <select name="vote_average" id="vote_average" class="form-select">
                        <option value="-">Any</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7" selected="selected">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>
                </div>

                <!-- Radio buttons -->
                <div class="col-auto">
                    <label for="sort_order" class="form-label me-2">Direction</label><br />
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="vote_average_direction" id="vote_average_gte" value="gte" checked>
                        <label class="form-check-label" for="vote_average_gte">Greater than</label>
                    </div>
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="vote_average_direction" id="vote_average_lte" value="lte">
                        <label class="form-check-label" for="vote_average_lte">Less than</label>
                    </div>
                </div>
            </div>

            <hr />

            <!-- Vote count -->
            <div class="row mb-3 align-items-center">
                <!-- Dropdown -->
                 <div class="mb-3 col-auto">
                    <label for="vote_count" class="form-label">Vote count</label>
                    <input type="number" name="vote_count" id="vote_count" class="form-control" value="1000">
                </div>

                <!-- Radio buttons -->
                <div class="col-auto">
                    <label for="sort_order" class="form-label me-2">Direction</label><br />
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="vote_count_direction" id="vote_count_gte" value="gte" checked>
                        <label class="form-check-label" for="vote_count_gte">Greater than</label>
                    </div>
                    <div class="form-check form-switch form-check-inline">
                        <input class="form-check-input" type="radio" name="vote_count_direction" id="vote_count_lte" value="lte">
                        <label class="form-check-label" for="vote_count_lte">Less than</label>
                    </div>
                </div>
            </div>

            <hr />

            <!-- With genres -->
            <div class="mb-3">
                <label class="form-label">With genres</label>
                <div class="row">
                    @foreach ($genres as $genre_id => $genre_name)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check form-switch">
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
