@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <h1>Give me movies</h1>

    @if (count($searches) > 0)
        <?php $i = 1; ?>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">View report</th>
                </tr>
            </thead>
            <tbody>
        @foreach($searches as $search)
                <tr>
                    <th scope="row">{{ $i++ }}</th>
                    <td>{{ $search->name }}</td>
                    <td>{{ $search->status }}</td>
                    @if ($search->status == 'done')
                        <td><a href="{{ route('search.report', ['id' => $search->id]) }}">Link</a></td>
                    @else
                        <td><a href="#">Link</a></td>
                    @endif
 
                </tr>
        @endforeach
            </tbody>
         </table>
    @else
        <p>Create a new <a href="{{ route('search.form.show') }}">SEARCH</a></p>
    @endif
@endsection
