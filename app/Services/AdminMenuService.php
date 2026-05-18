<?php

namespace App\Services;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminMenuService
{
    private const CANONICAL_CATEGORIES = [
        'Makanan',
        'Kopi',
        'Teh',
        'Susu',
    ];

    public function getAll()
    {
        $this->ensureCanonicalCategories();

        return Produk::with('kategori')
            ->latest('created_at')
            ->get();
    }

    public function getCategories()
    {
        $this->ensureCanonicalCategories();

        return KategoriProduk::whereIn('nama_kategori', self::CANONICAL_CATEGORIES)
            ->get()
            ->sortBy(fn ($category) => array_search($category->nama_kategori, self::CANONICAL_CATEGORIES, true))
            ->values();
    }

    public function create(array $data, ?UploadedFile $photo = null): Produk
    {
        $data = $this->normalizePayload($data);

        if ($photo) {
            $data['foto_produk'] = $this->storePhoto($photo);
        }

        return Produk::create($data)->load('kategori');
    }

    public function update(Produk $produk, array $data, ?UploadedFile $photo = null): Produk
    {
        $data = $this->normalizePayload($data);

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

    private function normalizePayload(array $data): array
    {
        if (array_key_exists('kategori_id', $data)) {
            $data['id_kategori'] = $data['kategori_id'];
            unset($data['kategori_id']);
        }

        if (! Schema::hasColumn('produks', 'opsi_suhu')) {
            unset($data['opsi_suhu']);
        } elseif (! array_key_exists('opsi_suhu', $data) || ! $data['opsi_suhu']) {
            $data['opsi_suhu'] = 'none';
        }

        $hasHotPriceColumn = Schema::hasColumn('produks', 'harga_hot');
        $hasIcePriceColumn = Schema::hasColumn('produks', 'harga_ice');

        if (! $hasHotPriceColumn) {
            unset($data['harga_hot']);
        }

        if (! $hasIcePriceColumn) {
            unset($data['harga_ice']);
        }

        $temperatureOption = $data['opsi_suhu'] ?? 'none';

        if ($hasHotPriceColumn && array_key_exists('harga_hot', $data) && $data['harga_hot'] === '') {
            $data['harga_hot'] = null;
        }

        if ($hasIcePriceColumn && array_key_exists('harga_ice', $data) && $data['harga_ice'] === '') {
            $data['harga_ice'] = null;
        }

        if ($temperatureOption === 'none') {
            if ($hasHotPriceColumn) {
                $data['harga_hot'] = null;
            }

            if ($hasIcePriceColumn) {
                $data['harga_ice'] = null;
            }
        }

        if ($temperatureOption === 'hot' && $hasIcePriceColumn) {
            $data['harga_ice'] = null;
        }

        if ($temperatureOption === 'ice' && $hasHotPriceColumn) {
            $data['harga_hot'] = null;
        }

        return $data;
    }

    private function ensureCanonicalCategories(): void
    {
        foreach (self::CANONICAL_CATEGORIES as $category) {
            KategoriProduk::firstOrCreate(['nama_kategori' => $category]);
        }
    }
}
