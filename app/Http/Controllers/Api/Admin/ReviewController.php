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

    public function destroy(Review $review)
    {
        $this->reviewService->delete($review);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil dihapus.',
        ]);
    }

    public function destroyPhoto(Review $review, int $photoIndex)
    {
        return response()->json([
            'success' => true,
            'message' => 'Foto review berhasil dihapus.',
            'data' => $this->reviewService->deletePhoto($review, $photoIndex),
        ]);
    }
}
