@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <h1>Give me movies</h1>

    @if (count($movies) > 0)
        <?php $i = 1;?>
        @foreach($movies as $movie)

            <div class="container my-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="row g-0 align-items-center">

                        {{-- Movie Poster --}}
                        <div class="col-md-3 text-center p-2">
                            @if($movie->poster_path)
                                <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}" 
                                    class="img-fluid rounded" 
                                    alt="{{ $movie->title }}"
                                    style="max-width: 100px;"> {{-- 🔹 smaller poster --}}
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                    style="height: 120px; width: 100px;">
                                    <span class="text-muted small">No Poster</span>
                                </div>
                            @endif
                        </div>

                        {{-- Movie Details --}}
                        <div class="col-md-9">
                            <div class="card-body py-2">
                                <h5 class="card-title mb-1">{{ $movie->title }}</h5>
                                <small class="text-muted d-block mb-2">
                                    Original: {{ $movie->original_title ?? 'N/A' }} | 
                                    Lang: {{ $movie->original_language ?? 'N/A' }} | 
                                    TMDB ID: {{ $movie->tmdb_id }}
                                </small>

                                {{-- Overview --}}
                                <p class="card-text small mb-2">
                                    {{ Str::limit($movie->overview ?? 'No description available.', 160) }}
                                </p>

                                <div class="row small">
                                    {{-- Column 1 --}}
                                    <div class="col-md-6">
                                        <p><strong>Release:</strong> {{ $movie->release_date ?? 'Unknown' }}</p>
                                        <p><strong>Popularity:</strong> {{ number_format($movie->popularity, 1) }}</p>
                                        <p><strong>⭐ Rating:</strong> {{ $movie->vote_average }} ({{ $movie->vote_count }} votes)</p>
                                        <p><strong>Has Details:</strong> {{ $movie->has_details ? 'Yes' : 'No' }}</p>
                                        <p><strong>Backdrop:</strong> {{ $movie->backdrop_path ?? 'N/A' }}</p>
                                    </div>

                                    {{-- Column 2 --}}
                                    <div class="col-md-6">
                                        <p><strong>Budget:</strong> {{ $movie->budget ?? 'N/A' }}</p>
                                        <p><strong>Revenue:</strong> {{ $movie->revenue ?? 'N/A' }}</p>
                                        <p><strong>Status:</strong> {{ $movie->status ?? 'N/A' }}</p>
                                        <p><strong>Tagline:</strong> <em>{{ $movie->tagline ?? 'N/A' }}</em></p>
                                        <p><strong>Origin Country:</strong> {{ $movie->origin_country ?? 'N/A' }}</p>
                                        @if($movie->homepage)
                                            <p><strong>Homepage:</strong> 
                                                <a href="{{ $movie->homepage }}" target="_blank">{{ $movie->homepage }}</a>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Genres --}}
                                <div class="d-flex flex-wrap small mt-2">
                                    <strong class="me-2">Genres:</strong>
                                    @if(isset($movie->genres) && count($movie->genres) > 0)
                                        @foreach($movie->genres as $genre)
                                            <span class="badge bg-secondary me-2">{{ $genre->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No genres data</span>
                                    @endif
                                </div>

                                {{-- External link --}}
                                <div class="mt-2">
                                    <a href="https://www.themoviedb.org/movie/{{ $movie->tmdb_id }}" target="_blank" 
                                    class="btn btn-sm btn-outline-primary">
                                        View on TMDB
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



        @endforeach
            </tbody>
         </table>
    @else
        <p>No movies</p>
    @endif
@endsection




























