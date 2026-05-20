<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

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
        foreach ($review->foto_review ?? [] as $photoUrl) {
            $this->deletePhotoFile($photoUrl);
        }

        $review->delete();
    }

    public function deletePhoto(Review $review, int $photoIndex): Review
    {
        $photos = array_values($review->foto_review ?? []);

        if (! array_key_exists($photoIndex, $photos)) {
            throw ValidationException::withMessages([
                'photo' => 'Foto review tidak ditemukan.',
            ]);
        }

        $this->deletePhotoFile($photos[$photoIndex]);
        array_splice($photos, $photoIndex, 1);

        $review->update([
            'foto_review' => $photos,
        ]);

        return $review->fresh();
    }

    private function deletePhotoFile(?string $photoUrl): void
    {
        if (! $photoUrl) {
            return;
        }

        $path = parse_url($photoUrl, PHP_URL_PATH) ?: $photoUrl;
        $path = ltrim($path, '/\\');

        if (! str_starts_with(str_replace('\\', '/', $path), 'uploads/reviews/')) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
