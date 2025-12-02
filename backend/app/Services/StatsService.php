<?php

namespace App\Services;

use App\Models\SearchQuery;
use App\Models\SearchStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function recalculate(): SearchStat
    {
        $baseQuery = SearchQuery::query();

        $totalSearches = (clone $baseQuery)->count();

        $topQueries = (clone $baseQuery)
            ->whereNotNull('term')
            ->where('term', '<>', '')
            ->select('term', DB::raw('COUNT(*) as hits'))
            ->groupBy('term')
            ->orderByDesc('hits')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'term' => $row->term,
                'hits' => (int) $row->hits,
            ])
            ->values()
            ->all();

        $avgResponseTime = (clone $baseQuery)->avg('response_time_ms');

        $byResource = (clone $baseQuery)
            ->select('resource', DB::raw('COUNT(*) as hits'))
            ->groupBy('resource')
            ->get()
            ->map(fn ($row) => [
                'resource' => $row->resource,
                'hits'     => (int) $row->hits,
            ])
            ->values()
            ->all();

        $busiestHour = (clone $baseQuery)
            ->select(DB::raw('EXTRACT(HOUR FROM searched_at) as hour'), DB::raw('COUNT(*) as hits'))
            ->groupBy('hour')
            ->orderByDesc('hits')
            ->limit(1)
            ->first();

        $busiestHourData = null;
        if ($busiestHour) {
            $busiestHourData = [
                'hour' => (int) $busiestHour->hour,
                'hits' => (int) $busiestHour->hits,
            ];
        }

        $payload = [
            'total_searches'   => $totalSearches,
            'top_queries'      => $topQueries,
            'avg_response_ms'  => $avgResponseTime ? round($avgResponseTime, 2) : null,
            'by_resource'      => $byResource,
            'busiest_hour'     => $busiestHourData,
            'generated_at'     => Carbon::now()->toIso8601String(),
        ];

        return SearchStat::create([
            'payload'       => $payload,
            'calculated_at' => now(),
        ]);
    }
}
