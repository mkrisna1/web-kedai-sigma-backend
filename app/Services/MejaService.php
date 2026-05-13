<?php

namespace App\Services;

use App\Models\Meja;

class MejaService
{
    public function getAll()
    {
        return Meja::orderBy('nomor_meja')->get();
    }

    public function create(array $data): Meja
    {
        $meja = Meja::create($data);

        return $this->generateQr($meja);
    }

    public function update(Meja $meja, array $data): Meja
    {
        $meja->update($data);

        return $meja->fresh();
    }

    public function delete(Meja $meja): void
    {
        $meja->delete();
    }

    public function generateQr(Meja $meja): Meja
    {
        $meja->update([
            'qr_code' => rtrim(env('FRONTEND_URL', 'http://127.0.0.1:5173'), '/') . "/qr/menu?meja_id={$meja->id}",
        ]);

        return $meja->fresh();
    }
}

