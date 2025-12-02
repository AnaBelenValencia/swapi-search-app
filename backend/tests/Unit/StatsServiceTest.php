<?php

namespace Tests\Unit;

use App\Models\SearchQuery;
use App\Models\SearchStat;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_stats_from_search_queries(): void
    {
        SearchQuery::create([
            'resource'         => 'people',
            'term'             => 'luke',
            'page'             => 1,
            'limit'            => 10,
            'results_count'    => 1,
            'response_time_ms' => 100,
            'searched_at'      => now()->setTime(10, 0),
        ]);

        SearchQuery::create([
            'resource'         => 'people',
            'term'             => 'vader',
            'page'             => 1,
            'limit'            => 10,
            'results_count'    => 1,
            'response_time_ms' => 200,
            'searched_at'      => now()->setTime(10, 30),
        ]);

        SearchQuery::create([
            'resource'         => 'films',
            'term'             => 'hope',
            'page'             => 1,
            'limit'            => 10,
            'results_count'    => 1,
            'response_time_ms' => 150,
            'searched_at'      => now()->setTime(11, 0),
        ]);

        /** @var StatsService $service */
        $service = app(StatsService::class);

        $stat = $service->recalculate();

        $this->assertInstanceOf(SearchStat::class, $stat);
        $this->assertEquals(3, $stat->payload['total_searches']);

        $this->assertEquals(150.0, $stat->payload['avg_response_ms']);

        $byResource = collect($stat->payload['by_resource'])->keyBy('resource');

        $this->assertEquals(2, $byResource['people']['hits']);
        $this->assertEquals(1, $byResource['films']['hits']);

        $this->assertEquals(1, SearchStat::count());
    }
}
