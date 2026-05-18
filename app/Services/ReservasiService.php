<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Reservasi;

class ReservasiService
{
    public function store(array $data): Reservasi
    {
        return Reservasi::create([
            'id_meja'           => Meja::query()->orderBy('nomor_meja')->value('id_meja') ?? 1,
            'nama_reservasi'    => $data['nama_reservasi'],
            'no_hp'             => $data['no_hp'],
            'tgl_reservasi'     => $data['tgl_reservasi'],
            'jam_reservasi'     => $data['jam_reservasi'],
            'jml_orang'         => $data['jml_orang'],
            'catatan_reservasi' => $data['catatan_reservasi'] ?? null,
            'status_reservasi'  => 'menunggu_konfirmasi',
        ]);
    }

    public function getAllForAdmin()
    {
        $this->deleteExpiredCancelledReservations();

        return Reservasi::with('meja')
            ->latest('created_at')
            ->get();
    }

    public function updateStatus(
        Reservasi $reservasi,
        string $statusReservasi,
        ?int $adminId = null,
        ?int $mejaId = null
    ): Reservasi {
        $data = [
            'status_reservasi' => $statusReservasi,
        ];

        if ($mejaId !== null) {
            $data['id_meja'] = $mejaId;
        }

        $reservasi->update($data);

        return $reservasi->fresh('meja');
    }

    private function deleteExpiredCancelledReservations(): void
    {
        Reservasi::query()
            ->where('status_reservasi', 'dibatalkan')
            ->where('updated_at', '<=', now()->subDays(3))
            ->delete();
    }
}
