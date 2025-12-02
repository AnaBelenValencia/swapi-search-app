<?php

namespace App\Http\Controllers;

use App\Models\SearchStat;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(StatsService $statsService): JsonResponse
    {
        $latest = SearchStat::latest('calculated_at')->first();

        if (!$latest) {
            $latest = $statsService->recalculate();
        }

        return response()->json([
            'stats' => $latest->payload,
            'calculated_at' => $latest->calulated_at?->toIso8601String(),
        ]);
    }
}