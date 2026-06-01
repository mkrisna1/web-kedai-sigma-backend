<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    private const MAX_TOTAL_PHOTO_SIZE_KB = 10240;

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
<<<<<<< HEAD
            'photos.*'       => 'image|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'photos.max' => 'Maksimal 5 foto review.',
            'photos.*.image' => 'File review harus berupa foto, bukan dokumen.',
            'photos.*.mimes' => 'Foto review hanya boleh JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto review maksimal 3MB.',
=======
            'photos.*'       => 'image|mimes:jpg,jpeg,png|max:2048',
>>>>>>> e8590a9 (benerin logika reservasi & pesanan)
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $photos = $this->file('photos', []);

            if (! is_array($photos)) {
                return;
            }

            $totalSizeKb = collect($photos)->sum(
                fn ($photo) => $photo ? $photo->getSize() / 1024 : 0,
            );

            if ($totalSizeKb > self::MAX_TOTAL_PHOTO_SIZE_KB) {
                $validator->errors()->add(
                    'photos',
                    'Total ukuran foto maksimal 10MB untuk 5 foto.',
                );
            }
        });
    }
}
