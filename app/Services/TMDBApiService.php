<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use App\Models\Search;

class TMDBApiService {
    private string $baseUrl = 'https://api.themoviedb.org/3';
    private string $api_key;
    private int $retries;
    private int $retriesInterval;

    public function __construct(){
        $this->api_key = config('services.tmdb.api_token');
        $this->retries = config('services.tmdb.retries');
        $this->retriesInterval = config('services.tmdb.retries_interval');
    }

    /**
     * Undocumented function
     *
     * @param string $routeName
     * @param string $url
     * @param array|null $params
     * @return void
     */
    public function callRoute(string $routeName, string $url, ?array $params = null) {
        try {
            $response = Http::withToken($this->api_key)
                ->retry($this->retries, $this->retriesInterval)
                ->acceptJson()
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data;
            } 

            throw new \Exception(
                "Error calling route {$routeName} (Status {$response->status()}): " . $response->body()
            );
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
        }
    }

    /**
     * Undocumented function
     *
     * @param array $preparedParams
     * @return void
     */
    public function discover(array $preparedParams) {
        $url = "{$this->baseUrl}/discover/movie";
        return $this->callRoute("discover", $url, $preparedParams);
    }

    /**
     * Undocumented function
     *
     * @param integer $movieId
     * @return void
     */
    public function getSingleMovieDetails(int $movieId){
        $url = "{$this->baseUrl}/movie/{$movieId}";
        return $this->callRoute("single_move_details", $url);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getGenres() {
        $url = "{$this->baseUrl}/genre/movie/list";
        return $this->callRoute("get_genres", $url);
    }
}
