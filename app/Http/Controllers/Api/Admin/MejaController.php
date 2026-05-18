<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Services\MejaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MejaController extends Controller
{
    public function __construct(
        protected MejaService $mejaService
    ) {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->mejaService->getAll(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_meja' => 'required|string|max:255|unique:mejas,nomor_meja',
            'status_meja' => 'required|in:active,maintenance',
            'capacity' => 'nullable|integer|min:1|max:50',
            'used_seats' => 'nullable|integer|min:0|max:50',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil ditambahkan.',
            'data' => $this->mejaService->create(
                $data,
                $this->frontendOrigin($request)
            ),
        ], 201);
    }

    public function update(Request $request, Meja $meja)
    {
        $data = $request->validate([
            'nomor_meja' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('mejas', 'nomor_meja')->ignore($meja->getKey(), 'id_meja'),
            ],
            'status_meja' => 'sometimes|required|in:active,maintenance',
            'capacity' => 'sometimes|required|integer|min:1|max:50',
            'used_seats' => 'sometimes|required|integer|min:0|max:50',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil diperbarui.',
            'data' => $this->mejaService->update($meja, $data),
        ]);
    }

    public function destroy(Meja $meja)
    {
        $this->mejaService->delete($meja);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil dihapus.',
        ]);
    }

    public function generateQr(Request $request, Meja $meja)
    {
        return response()->json([
            'success' => true,
            'message' => 'QR meja berhasil dibuat.',
            'data' => $this->mejaService->generateQr(
                $meja,
                $this->frontendOrigin($request)
            ),
        ]);
    }

    private function frontendOrigin(Request $request): ?string
    {
        return $request->headers->get('origin');
    }
}
