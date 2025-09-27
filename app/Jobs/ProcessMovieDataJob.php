<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log; 
use App\Services\TMDBService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Search;

class ProcessMovieDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $searchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $searchId)
    {
        $this->searchId = $searchId;
    }

    /**
     * Execute the job.
     */
    public function handle(TMDBService $tmdb): void
    {
        Log::info('STARTING JOB - movie processing');
        try {
            $tmdb->getMovies($this->searchId);
        } catch (\Exception $e) {
            $search = Search::find($this->searchId);
            $search->status = "error";
            $search->save();

            Log::error($e->getMessage());
        }
        Log::info('ENDING JOB - movie processing');
    }
}
