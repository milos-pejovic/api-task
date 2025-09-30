<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 

class TMDBApiService {
    private string $baseUrl = 'https://api.themoviedb.org/3';
    private string $api_key;
    private int $retries;
    private int $retriesInterval;

    public function __construct(){
        $this->api_key = config('services.tmdb.api_token');
        $this->retries = config('services.tmdb.retries'); // Default: 3 times
        $this->retriesInterval = config('services.tmdb.retries_interval'); // Default: 1 second
    }

    /**
     * callRoute
     *
     * @param string $routeName
     * @param string $url
     * @param array|null $params
     * @return ?array
     */
    public function callRoute(string $routeName, string $url, ?array $params = null) : ?array
    {
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
            Log::error("[API] Failed calling route {$routeName} (Status {$response->status()}): " . $response->body());
            return null;
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
        $parameters = ['append_to_response' => 'credits'];
        return $this->callRoute("single_move_details", $url, $parameters);
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
