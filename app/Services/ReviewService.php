<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Http\UploadedFile;

class ReviewService
{
    public function store(array $data, array $photos = []): Review
    {
        return Review::create([
            'id_pesanan'     => $data['pesanan_id'] ?? null,
            'nama_reviewer'  => $data['nama_pelanggan'],
            'rating'         => $data['rating'],
            'komentar'       => $data['komentar'],
            'foto_review'    => $this->storePhotos($photos),
            'waktu_dibuat'   => now(),
        ]);
    }

    public function getAll()
    {
        return Review::query()
            ->orderBy('waktu_dibuat', 'desc')
            ->get();
    }

    public function like(Review $review): Review
    {
        $review->increment('likes_count');

        return $review->fresh();
    }

    private function storePhotos(array $photos): array
    {
        $seenHashes = [];

        return collect($photos)
            ->filter(fn ($photo) => $photo instanceof UploadedFile)
            ->filter(function (UploadedFile $photo) use (&$seenHashes) {
                $hash = hash_file('sha256', $photo->getRealPath());

                if (isset($seenHashes[$hash])) {
                    return false;
                }

                $seenHashes[$hash] = true;

                return true;
            })
            ->take(5)
            ->map(function (UploadedFile $photo) {
                $mimeType = $photo->getMimeType() ?: 'image/jpeg';
                $contents = file_get_contents($photo->getRealPath());

                return "data:{$mimeType};base64,".base64_encode($contents);
            })
            ->values()
            ->all();
    }
}
