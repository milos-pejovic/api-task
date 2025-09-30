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

                        <!-- Movie poster -->
                        <div class="col-md-3 text-center p-2">
                            @if($movie->poster_path)
                                <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}" 
                                    class="img-fluid rounded" 
                                    alt="{{ $movie->title }}"
                                    style="max-width: 300px;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                    style="height: 120px; width: 300px;">
                                    <span class="text-muted small">No Poster</span>
                                </div>
                            @endif
                        </div>

                        <!-- Movie details -->
                        <div class="col-md-9">
                            <div class="card-body py-2">
                                <h3 class="card-title mb-1">{{ $movie->title }}</h3>
                                <small class="text-muted d-block mb-2">
                                    Original: {{ $movie->original_title ?? 'N/A' }} | 
                                    Lang: {{ $movie->original_language ?? 'N/A' }} | 
                                    TMDB ID: {{ $movie->tmdb_id }}
                                </small>

                                <p class="card-text small mb-2">
                                    {{ Str::limit($movie->overview ?? 'No description available.', 500) }}
                                </p>

                                <div class="row small">
                                    <div class="col-md-6">
                                        <p><strong>Release:</strong> {{ $movie->release_date ?? 'Unknown' }}</p>
                                        <p><strong>Popularity:</strong> {{ number_format($movie->popularity, 1) }}</p>
                                        <p><strong>⭐ Rating:</strong> {{ $movie->vote_average }} ({{ $movie->vote_count }} votes)</p>
                                        <p><strong>Has Details:</strong> {{ $movie->has_details ? 'Yes' : 'No' }}</p>
                                        <p><strong>Backdrop:</strong> {{ $movie->backdrop_path ?? 'N/A' }}</p>
                                    </div>

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

                                <div class="small mt-3">
                                    <strong class="d-block mb-2">Crew:</strong>
                                    
                                    @php
                                        $visibleCrew = $movie->crew->take(15);
                                        $hiddenCrew = $movie->crew->slice(15);
                                    @endphp

                                    <div class="row g-2">
                                        @foreach($visibleCrew as $member)
                                            <div class="col-md-4 d-flex align-items-center">
                                                {{-- Profile image --}}
                                                @if($member->profile_path)
                                                    <img src="https://image.tmdb.org/t/p/w185{{ $member->profile_path }}" 
                                                        alt="{{ $member->name }}"
                                                        class="rounded me-2"
                                                        style="width:40px; height:40px; object-fit:cover;">
                                                @else
                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                        style="width:40px; height:40px;">
                                                        <span class="text-muted small">N/A</span>
                                                    </div>
                                                @endif

                                                <div>
                                                    <span class="fw-bold">{{ $member->name }}</span><br>
                                                    <small class="text-muted">{{ $member->pivot->role }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($hiddenCrew->count() > 0)
                                        <div class="collapse mt-2" id="moreCrew{{ $movie->id }}">
                                            <div class="row g-2">
                                                @foreach($hiddenCrew as $member)
                                                    <div class="col-md-4 d-flex align-items-center">
                                                        @if($member->profile_path)
                                                            <img src="https://image.tmdb.org/t/p/w185{{ $member->profile_path }}" 
                                                                alt="{{ $member->name }}"
                                                                class="rounded me-2"
                                                                style="width:40px; height:40px; object-fit:cover;">
                                                        @else
                                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                                style="width:40px; height:40px;">
                                                                <span class="text-muted small">N/A</span>
                                                            </div>
                                                        @endif

                                                        <div>
                                                            <span class="fw-bold">{{ $member->name }}</span><br>
                                                            <small class="text-muted">{{ $member->pivot->role }}</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <button class="btn btn-sm btn-outline-secondary mt-2" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#moreCrew{{ $movie->id }}">
                                            Show more
                                        </button>
                                    @endif
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
