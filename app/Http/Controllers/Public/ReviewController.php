<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreReviewRequest;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        $reviews = $this->reviewService->getAll();

        return response()->json([
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = $this->reviewService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil dikirim, terima kasih!',
            'data'    => $review,
        ], 201);
    }
}