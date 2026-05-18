<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'required|string|max:255',
            'rating'         => 'required|integer|min:1|max:5',
            'komentar'       => 'required|string',
            'photos'         => 'nullable|array|max:5',
            'photos.*'       => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
