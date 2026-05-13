<?php

namespace App\Services;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminMenuService
{
    public function getAll()
    {
        return Produk::with('kategori')
            ->latest('created_at')
            ->get();
    }

    public function getCategories()
    {
        return KategoriProduk::orderBy('nama_kategori')->get();
    }

    public function create(array $data, ?UploadedFile $photo = null): Produk
    {
        if ($photo) {
            $data['foto_produk'] = $this->storePhoto($photo);
        }

        return Produk::create($data)->load('kategori');
    }

    public function update(Produk $produk, array $data, ?UploadedFile $photo = null): Produk
    {
        if ($photo) {
            $this->deletePhoto($produk->foto_produk);
            $data['foto_produk'] = $this->storePhoto($photo);
        }

        $produk->update($data);

        return $produk->fresh('kategori');
    }

    public function delete(Produk $produk): void
    {
        $produk->delete();
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $photo->store('menu', 'public');

        return "/storage/{$path}";
    }

    private function deletePhoto(?string $photoUrl): void
    {
        if (!$photoUrl) {
            return;
        }

        $path = parse_url($photoUrl, PHP_URL_PATH) ?: $photoUrl;

        if (!str_starts_with($path, '/storage/menu/')) {
            return;
        }

        Storage::disk('public')->delete(substr($path, strlen('/storage/')));
    }
}

