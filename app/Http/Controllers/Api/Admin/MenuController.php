<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\AdminMenuService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function __construct(
        protected AdminMenuService $menuService
    ) {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->menuService->getAll(),
        ]);
    }

    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => $this->menuService->getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeRequest($request);

        $data = $request->validate($this->rules());

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $this->menuService->create($data, $request->file('foto_produk')),
        ], 201);
    }

    public function update(Request $request, Produk $produk)
    {
        $this->normalizeRequest($request);

        $data = $request->validate($this->rules(false, $produk));

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui.',
            'data' => $this->menuService->update($produk, $data, $request->file('foto_produk')),
        ]);
    }

    public function destroy(Produk $produk)
    {
        $this->menuService->delete($produk);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus.',
        ]);
    }

    private function rules(bool $required = true, ?Produk $produk = null): array
    {
        $requiredRules = $required ? ['required'] : ['sometimes', 'required'];

        return [
            'kategori_id' => [...$requiredRules, 'exists:kategori_produks,id_kategori'],
            'nama_produk' => [
                ...$requiredRules,
                'string',
                'max:255',
                Rule::unique('produks', 'nama_produk')->ignore($produk?->getKey(), 'id_produk'),
            ],
            'harga_produk' => [...$requiredRules, 'numeric', 'min:0'],
            'harga_hot' => ['nullable', 'numeric', 'min:0'],
            'harga_ice' => ['nullable', 'numeric', 'min:0'],
            'deskripsi_produk' => ['nullable', 'string'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'opsi_suhu' => ['nullable', 'in:none,hot,ice,hot_ice'],
            'ketersediaan_produk' => [...$requiredRules, 'in:tersedia,tidak_tersedia'],
        ];
    }

    private function normalizeRequest(Request $request): void
    {
        if ($request->has('nama_produk')) {
            $request->merge([
                'nama_produk' => trim((string) $request->input('nama_produk')),
            ]);
        }
    }
}
