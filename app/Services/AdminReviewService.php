<?php

namespace App\Services;

use App\Models\Review;

class AdminReviewService
{
    public function getAll()
    {
        return Review::with('admin')
            ->latest('created_at')
            ->get();
    }

    public function reply(Review $review, string $reply, ?int $adminId = null): Review
    {
        $review->update([
            'balasan_admin' => $reply,
            'admin_id' => $adminId,
        ]);

        return $review->fresh('admin');
    }
}
