<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'period' => 'nullable|in:day,week,month,year',
            'date' => 'nullable|date',
            'traffic_month' => 'nullable|integer|min:1|max:12',
            'traffic_year' => 'nullable|integer|min:2020|max:2100',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->summary(
                $data['period'] ?? 'day',
                $data['date'] ?? null,
                $data['traffic_month'] ?? null,
                $data['traffic_year'] ?? null
            ),
        ]);
    }
}
