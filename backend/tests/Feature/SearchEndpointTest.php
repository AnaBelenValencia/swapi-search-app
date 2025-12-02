<?php

namespace Tests\Feature;

use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_people_returns_normalized_items_and_logs_query(): void
    {
        // Fake SWAPI.dev search for people
        Http::fake([
            'https://swapi.dev/api/people*' => Http::response([
                'count'    => 1,
                'next'     => null,
                'previous' => null,
                'results'  => [
                    [
                        'name'       => 'Luke Skywalker',
                        'birth_year' => '19BBY',
                        'url'        => 'https://swapi.dev/api/people/1/',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/search?resource=people&q=luke&page=1&limit=5');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'type',
                        'label',
                        'subtitle',
                    ],
                ],
                'meta' => [
                    'page',
                    'perPage',
                    'total',
                    'totalPages',
                    'resource',
                    'query',
                    'responseTimeMs',
                ],
            ]);

        $this->assertSame('people', $response->json('meta.resource'));
        $this->assertSame('luke', $response->json('meta.query'));
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('1', $response->json('data.0.id'));
        $this->assertSame('Luke Skywalker', $response->json('data.0.label'));

        // SearchQuery should be logged
        $this->assertDatabaseHas('search_queries', [
            'resource' => 'people',
            'term'     => 'luke',
            'page'     => 1,
            'limit'    => 5,
        ]);

        $this->assertEquals(1, SearchQuery::count());
    }

    public function test_search_with_invalid_resource_returns_400(): void
    {
        $response = $this->getJson('/api/search?resource=planets&q=tatooine');

        $response
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid resource. Supported resources are: people, films',
            ]);
    }

    public function test_show_person_returns_details_and_movies(): void
    {
        // Fake SWAPI.dev person + film
        Http::fake([
            'https://swapi.dev/api/people/1' => Http::response([
                'name'       => 'Luke Skywalker',
                'birth_year' => '19BBY',
                'gender'     => 'male',
                'eye_color'  => 'blue',
                'hair_color' => 'blond',
                'height'     => '172',
                'mass'       => '77',
                'films'      => [
                    'https://swapi.dev/api/films/1/',
                ],
                'url'        => 'https://swapi.dev/api/people/1/',
            ], 200),

            'https://swapi.dev/api/films/1/' => Http::response([
                'title' => 'A New Hope',
                'url'   => 'https://swapi.dev/api/films/1/',
            ], 200),
        ]);

        $response = $this->getJson('/api/people/1');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'details' => [
                    'birthYear',
                    'gender',
                    'eyeColor',
                    'hairColor',
                    'height',
                    'mass',
                ],
                'movies' => [
                    [
                        'id',
                        'title',
                    ],
                ],
            ]);

        $this->assertSame('1', $response->json('id'));
        $this->assertSame('Luke Skywalker', $response->json('name'));
        $this->assertSame('19BBY', $response->json('details.birthYear'));

        $movies = $response->json('movies');
        $this->assertCount(1, $movies);
        $this->assertSame('1', $movies[0]['id']);
        $this->assertSame('A New Hope', $movies[0]['title']);
    }

    public function test_show_film_returns_summary_and_characters(): void
    {
        Http::fake([
            'https://swapi.dev/api/films/1' => Http::response([
                'title'         => 'Return of the Jedi',
                'opening_crawl' => 'Luke Skywalker has returned to his home planet...',
                'characters'    => [
                    'https://swapi.dev/api/people/1/',
                    'https://swapi.dev/api/people/2/',
                ],
                'url'           => 'https://swapi.dev/api/films/1/',
            ], 200),

            'https://swapi.dev/api/people/1/' => Http::response([
                'result' => [
                    'properties' => [
                        'name' => 'Luke Skywalker',
                        'url'  => 'https://swapi.dev/api/people/1/',
                    ],
                ],
            ], 200),

            'https://swapi.dev/api/people/2/' => Http::response([
                'result' => [
                    'properties' => [
                        'name' => 'C-3PO',
                        'url'  => 'https://swapi.dev/api/people/2/',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/films/1');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'title',
                'summary',
                'characters' => [
                    [
                        'id',
                        'name',
                    ],
                ],
            ]);

        $this->assertSame('1', $response->json('id'));
        $this->assertSame('Return of the Jedi', $response->json('title'));

        $this->assertStringStartsWith(
            'Luke Skywalker has returned',
            $response->json('summary')
        );

        $characters = $response->json('characters');
        $this->assertCount(2, $characters);

        $this->assertSame('1', $characters[0]['id']);
        $this->assertSame('Luke Skywalker', $characters[0]['name']);

        $this->assertSame('2', $characters[1]['id']);
        $this->assertSame('C-3PO', $characters[1]['name']);
    }
}
