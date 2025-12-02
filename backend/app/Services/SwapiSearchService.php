<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SwapiSearchService
{

    private string $baseUrl = 'https://swapi.dev/api';

    /**
     * Search Star Wars characters/films by name with pagination.
     *
     * @param string      $resource "people" | "films"
     * @param string|null $term  Search term (name)
     * @param int         $page  Page number (1-based)
     * @param int         $limit Items per page
     *
     * @return array Decoded JSON from SWAPI
     */
    public function search(string $resource, ?string $term = null, int $page = 1, int $limit = 10): array
    {
        $resource = in_array($resource, ['people', 'films']) ? $resource : 'people';

        $url = $this->baseUrl . '/' . $resource . '/';

        $query = ['page' => $page];

        if (!empty($term)) {
            $query[$resource === 'people' ? 'name' : 'title'] = $term;
        }
        
        $response = Http::get($url, $query);

        $response->throw();
        return $response->json();
    }

     public function getById(string $resource, string|int $id): array
    {
        $url = "{$this->baseUrl}/{$resource}/{$id}";

        $response = Http::get($url);

        $response->throw();

        return $response->json();
    }

    public function getByUrl(string $url): array
    {
        $response = Http::get($url);

        $response->throw();

        return $response->json();
    }

    /**
     * Fetch multiple URLs concurrently.
     *
     * @param array $urls List of URLs to fetch
     * @return array Associative array [url => response_data]
     */
    public function getManyByUrl(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        $responses = Http::pool(fn ($pool) => 
            array_map(fn ($url) => $pool->get($url), $urls)
        );

        $results = [];
        foreach ($urls as $index => $url) {
            $response = $responses[$index] ?? null;
            if ($response && $response->successful()) {
                $results[$url] = $response->json();
            }
        }

        return $results;
    }
}
