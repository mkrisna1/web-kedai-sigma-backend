<?php

namespace App\Services;

use App\Models\Review;

class AdminReviewService
{
    public function getAll()
    {
        return Review::query()
            ->latest('waktu_dibuat')
            ->get();
    }

    public function reply(Review $review, string $reply, ?int $adminId = null): Review
    {
        $review->update([
            'balasan_admin' => $reply,
        ]);

        return $review->fresh();
    }

    public function delete(Review $review): void
    {
        $review->delete();
    }
}
