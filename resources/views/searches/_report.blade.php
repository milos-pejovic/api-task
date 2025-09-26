@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <h1>Give me movies</h1>

    @if (count($movies) > 0)
        <?php $i = 1; ?>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Release date</th>
                    <th scope="col">Release year</th>
                    <th scope="col">Overview</th>
                </tr>
            </thead>
            <tbody>
        @foreach($movies as $movie)
                <tr>
                    <th scope="row">{{ $i++ }}</th>
                    <td>{{ $movie->title }}</td>
                    <td>{{ $movie->release_date }}</td>
                    <td>{{ $movie->release_year }}</td>
                    <td>{{ $movie->overview }}</td>
                </tr>
        @endforeach
            </tbody>
         </table>
    @else
        <p>No movies</p>
    @endif
@endsection
