@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <h1>Give me movies</h1>

    @if (count($movies) > 0)
        <?php $i = 1; ?>
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
                                    Lang: {{ $movie->original_language ?? 'N/A' }}
                                </small>

                                <p class="card-text small mb-2">
                                    {{ Str::limit($movie->overview ?? 'No description available.', 120) }}
                                </p>

                                <div class="d-flex flex-wrap small">
                                    <span class="me-3"><strong>Release:</strong> {{ $movie->release_date ?? 'Unknown' }}</span>
                                    <span class="me-3"><strong>Pop:</strong> {{ number_format($movie->popularity, 1) }}</span>
                                    <span class="me-3"><strong>⭐</strong> {{ $movie->vote_average }} ({{ $movie->vote_count }})</span>
                                    <span><strong>ID:</strong> {{ $movie->tmdb_id }}</span>
                                </div>

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




























