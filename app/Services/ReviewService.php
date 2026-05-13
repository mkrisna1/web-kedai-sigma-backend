<?php

namespace App\Services;

use App\Models\Review;

class ReviewService
{
    public function store(array $data): Review
    {
        return Review::create([
            'nama_pelanggan' => $data['nama_pelanggan'],
            'rating'         => $data['rating'],
            'komentar'       => $data['komentar'],
        ]);
    }

    public function getAll()
    {
        return Review::with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}