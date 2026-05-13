<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\AdminReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected AdminReviewService $reviewService
    ) {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->reviewService->getAll(),
        ]);
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate([
            'balasan_admin' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan review berhasil disimpan.',
            'data' => $this->reviewService->reply(
                $review,
                $data['balasan_admin'],
                $request->user()?->id
            ),
        ]);
    }
}
