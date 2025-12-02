<?php

namespace App\Http\Controllers;

use App\Models\SearchQuery;
use App\Services\SwapiSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    /**
     * Main search endpoint (people / films).
     */
    public function search(Request $request, SwapiSearchService $swapi): JsonResponse
    {
        $term     = $request->query('q', $request->query('query'));
        $page     = max((int) $request->query('page', 1), 1);
        $limit    = max(min((int) $request->query('limit', 10), 50), 1);
        $resource = $request->query('resource', $request->query('type', 'people'));

        if (!in_array($resource, ['people', 'films'], true)) {
            return response()->json([
                'error'   => 'Invalid resource. Supported resources are: people, films',
                'message' => 'Please specify a valid resource',
            ], 400);
        }

        $start = microtime(true);

        try {
            $data = $swapi->search($resource, $term, $page);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to fetch data from SWAPI',
                'message' => $e->getMessage(),
            ], 502);
        }

        $elapsedMs = (microtime(true) - $start) * 1000;

        $results      = $data['results'] ?? [];
        $resultsCount = is_countable($results) ? count($results) : 0;

        // Persist query stats
        SearchQuery::create([
            'resource'         => $resource,
            'term'             => $term,
            'page'             => $page,
            'limit'            => $limit,
            'results_count'    => $resultsCount,
            'response_time_ms' => $elapsedMs,
            'searched_at'      => now(),
        ]);

        // Map results
        $items = collect($results)
            ->map(fn(array $item) => $this->mapSearchItem($resource, $item))
            ->values()
            ->all();

        return response()->json([
            'data' => $items,
            'meta' => [
                'page'           => $page,
                'perPage'        => $limit,
                'total'          => $resultsCount,
                'totalPages'     => null, // swapi.tech does NOT give a real count
                'resource'       => $resource,
                'query'          => $term,
                'responseTimeMs' => round($elapsedMs, 2),
            ],
        ]);
    }

    /**
     * DETAIL: Person
     */
    public function showPerson(string $id, SwapiSearchService $swapi): JsonResponse
    {
        try {
            $person = $swapi->getById('people', $id);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to fetch person',
                'message' => $e->getMessage(),
            ], 502);
        }

        if (!$person) {
            return response()->json(['error' => 'Person not found'], 404);
        }

        // Load movies concurrently
        $filmUrls = $person['films'] ?? [];
        $filmsData = $swapi->getManyByUrl($filmUrls);

        $movies = [];
        foreach ($filmUrls as $filmUrl) {
            $film = $filmsData[$filmUrl] ?? null;
            if ($film) {
                $movies[] = [
                    'id'    => $this->extractIdFromUrl($film['url'] ?? $filmUrl),
                    'title' => $film['title'] ?? 'Unknown',
                ];
            }
        }

        return response()->json([
            'id'      => $id,
            'name'    => $person['name'],
            'details' => [
                'birthYear' => $person['birth_year'],
                'gender'    => $person['gender'],
                'eyeColor'  => $person['eye_color'],
                'hairColor' => $person['hair_color'],
                'height'    => $person['height'],
                'mass'      => $person['mass'],
            ],
            'movies' => $movies,
        ]);
    }

    /**
     * DETAIL: Film
     */
    public function showFilm(string $id, SwapiSearchService $swapi): JsonResponse
    {
        try {
            $film = $swapi->getById('films', $id);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to fetch film',
                'message' => $e->getMessage(),
            ], 502);
        }

        if (!$film) {
            return response()->json(['error' => 'Film not found'], 404);
        }

        // Load characters concurrently
        $charUrls = $film['characters'] ?? [];
        $charsData = $swapi->getManyByUrl($charUrls);

        $characters = [];
        foreach ($charUrls as $charUrl) {
            $char = $charsData[$charUrl] ?? null;
            if ($char) {
                $characters[] = [
                    'id'   => $this->extractIdFromUrl($char['url'] ?? $charUrl),
                    'name' => $char['name'] ?? 'Unknown',
                ];
            }
        }

        return response()->json([
            'id'           => $id,
            'title'        => $film['title'],
            'summary'      => $film['opening_crawl'],
            'characters'   => $characters,
        ]);
    }

    /**
     * Normalize search item (list view)
     */
    private function mapSearchItem(string $resource, array $item): array
    {
        return match ($resource) {
            'people' => $this->mapPeopleListItem($item),
            'films'  => $this->mapFilmListItem($item),
            default  => $this->mapGenericListItem($resource, $item),
        };
    }

    private function mapPeopleListItem(array $item): array
    {
        $id   = $item['uid'] ?? $this->extractIdFromUrl($item['url'] ?? '');
        $name = $item['name'] ?? ($item['properties']['name'] ?? 'Unknown');

        return [
            'id'       => (string) $id,
            'type'     => 'people',
            'label'    => $name,
            'subtitle' => $item['properties']['birth_year'] ?? null,
        ];
    }

    private function mapFilmListItem(array $item): array
    {
        $id   = $item['uid'] ?? $this->extractIdFromUrl($item['url'] ?? '');
        $name = $item['title'] ?? ($item['properties']['title'] ?? 'Unknown');

        return [
            'id'       => (string) $id,
            'type'     => 'films',
            'label'    => $name,
            'subtitle' => $item['properties']['director'] ?? null,
        ];
    }

    private function mapGenericListItem(string $resource, array $item): array
    {
        $id = $item['uid'] ?? $this->extractIdFromUrl($item['url'] ?? '');

        return [
            'id'       => (string) $id,
            'type'     => $resource,
            'label'    => $item['name']
                ?? $item['title']
                ?? ($item['properties']['name'] ?? $item['properties']['title'] ?? 'Unknown'),
            'subtitle' => null,
        ];
    }

    private function extractIdFromUrl(?string $url): string
    {
        if (!$url) {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return '';
        }

        return basename(rtrim($path, '/'));
    }
}
