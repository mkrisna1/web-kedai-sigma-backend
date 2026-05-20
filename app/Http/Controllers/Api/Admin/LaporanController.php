<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService
    ) {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'period' => 'nullable|in:day,week,month',
            'date' => 'nullable|date',
            'export_period' => 'nullable|in:day,month,year',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->laporanService->summary(
                $data['period'] ?? 'day',
                $data['date'] ?? null,
                $data['export_period'] ?? 'day'
            ),
        ]);
    }
}
