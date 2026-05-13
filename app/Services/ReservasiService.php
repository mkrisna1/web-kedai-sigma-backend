<?php

namespace App\Services;

use App\Models\Reservasi;

class ReservasiService
{
    public function store(array $data): Reservasi
    {
        return Reservasi::create([
            'meja_id'           => null,
            'admin_id'          => null,
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
        return Reservasi::with(['meja', 'admin'])
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
            'admin_id' => $adminId,
            'status_reservasi' => $statusReservasi,
        ];

        if ($mejaId !== null) {
            $data['meja_id'] = $mejaId;
        }

        $reservasi->update($data);

        return $reservasi->fresh(['meja', 'admin']);
    }
}
