<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReservasiService
{
    public function __construct(
        protected TableAvailabilityService $tableAvailabilityService
    ) {
    }

    public function getAvailableTables(
        ?int $guestCount = null,
        ?string $reservationDate = null,
        ?string $reservationTime = null
    ): Collection
    {
        $this->tableAvailabilityService->releaseStaleOccupiedTables();

        $query = Meja::query()
            ->where('status_meja', 'active')
            ->orderBy('nomor_meja');

        if (! $reservationDate || $this->isReservationForToday($reservationDate)) {
            $query->where('used_seats', '<=', 0);
        }

        if ($guestCount !== null) {
            $query->where('capacity', '>=', $guestCount);

            if ($guestCount <= 4) {
                $query->where('capacity', '<=', 4);
            }
        }

        if ($reservationDate) {
            $query->whereDoesntHave('reservasis', function ($reservationQuery) use ($reservationDate) {
                $reservationQuery
                    ->whereDate('tgl_reservasi', Carbon::parse($reservationDate)->toDateString())
                    ->whereIn('status_reservasi', ['menunggu_konfirmasi', 'dikonfirmasi']);
            });
        }

        return $query->get();
    }

    public function store(array $data): Reservasi
    {
        $this->tableAvailabilityService->releaseStaleOccupiedTables();

        $meja = $this->resolveAvailableTable(
            (int) $data['meja_id'],
            (int) $data['jml_orang'],
            $data['tgl_reservasi'],
            $data['jam_reservasi']
        );

        return Reservasi::create([
            'id_meja'           => $meja->getKey(),
            'nama_reservasi'    => $data['nama_reservasi'],
            'no_hp'             => $data['no_hp'],
            'tgl_reservasi'     => $data['tgl_reservasi'],
            'jam_reservasi'     => $data['jam_reservasi'],
            'jml_orang'         => $data['jml_orang'],
            'catatan_reservasi' => $data['catatan_reservasi'] ?? null,
            'status_reservasi'  => 'menunggu_konfirmasi',
        ]);
    }

    public function getAllForAdmin(?string $date = null, ?string $status = null)
    {
        $this->deleteExpiredCancelledReservations();

        return Reservasi::with('meja')
            ->when($date, fn ($query) => $query->whereDate('tgl_reservasi', $date))
            ->when($status, fn ($query) => $query->where('status_reservasi', $status))
            ->latest('created_at')
            ->get();
    }

    public function updateStatus(
        Reservasi $reservasi,
        string $statusReservasi,
        ?int $adminId = null,
        ?int $mejaId = null
    ): Reservasi {
        return DB::transaction(function () use ($reservasi, $statusReservasi, $adminId, $mejaId) {
            $previousStatus = $reservasi->status_reservasi;
            $previousMejaId = $reservasi->id_meja;
            $data = [
                'status_reservasi' => $statusReservasi,
            ];

            if ($statusReservasi !== 'menunggu_konfirmasi' && Schema::hasColumn('reservasis', 'is_notif_read')) {
                $data['is_notif_read'] = true;
            }

            if ($adminId !== null) {
                $data['id_admin'] = $adminId;
            }

            if ($mejaId !== null) {
                $data['id_meja'] = $mejaId;
            }

            if (
                in_array($statusReservasi, ['menunggu_konfirmasi', 'dikonfirmasi'], true) &&
                ($data['id_meja'] ?? $reservasi->id_meja)
            ) {
                $this->ensureNoScheduleConflict(
                    (int) ($data['id_meja'] ?? $reservasi->id_meja),
                    $reservasi->tgl_reservasi,
                    $reservasi->jam_reservasi,
                    $reservasi->getKey()
                );
            }

            $reservasi->update($data);
            $reservasi->refresh();

            if ($statusReservasi === 'dikonfirmasi') {
                $this->markReservedTableUsed($reservasi, $previousStatus, $previousMejaId);
            }

            if (in_array($statusReservasi, ['selesai', 'dibatalkan'], true)) {
                $this->releaseTableWhenUnused($reservasi);
            }

            return $reservasi->fresh('meja');
        });
    }

    public function delete(Reservasi $reservasi): void
    {
        DB::transaction(function () use ($reservasi) {
            $mejaId = $reservasi->id_meja;
            $reservasiId = $reservasi->getKey();

            $reservasi->delete();

            if ($mejaId) {
                $this->releaseTableWhenUnusedById($mejaId, $reservasiId);
            }
        });
    }

    private function deleteExpiredCancelledReservations(): void
    {
        Reservasi::query()
            ->where('status_reservasi', 'dibatalkan')
            ->where('updated_at', '<=', now()->subDays(3))
            ->delete();
    }

    private function resolveAvailableTable(
        int $mejaId,
        int $guestCount,
        ?string $reservationDate = null,
        ?string $reservationTime = null,
        ?int $ignoredReservasiId = null
    ): Meja
    {
        $meja = Meja::find($mejaId);

        if (! $meja || $meja->status_meja !== 'active') {
            throw ValidationException::withMessages([
                'meja_id' => 'Meja tidak tersedia.',
            ]);
        }

        if ((int) $meja->capacity < $guestCount) {
            throw ValidationException::withMessages([
                'meja_id' => 'Kapasitas meja tidak cukup untuk jumlah orang.',
            ]);
        }

        if ($guestCount <= 4 && (int) $meja->capacity > 4) {
            throw ValidationException::withMessages([
                'meja_id' => 'Pilih meja dengan kapasitas maksimal 4 orang.',
            ]);
        }

        if (
            (! $reservationDate || $this->isReservationForToday($reservationDate)) &&
            (int) $meja->used_seats > 0
        ) {
            throw ValidationException::withMessages([
                'meja_id' => 'Meja sedang terpakai.',
            ]);
        }

        if ($reservationDate && $reservationTime) {
            $this->ensureNoScheduleConflict(
                $meja->getKey(),
                $reservationDate,
                $reservationTime,
                $ignoredReservasiId
            );
        }

        return $meja;
    }

    private function markReservedTableUsed(
        Reservasi $reservasi,
        ?string $previousStatus,
        ?int $previousMejaId
    ): void {
        if (! $reservasi->id_meja) {
            throw ValidationException::withMessages([
                'meja_id' => 'Pilih meja reservasi terlebih dahulu.',
            ]);
        }

        if (! $this->isReservationForToday($reservasi->tgl_reservasi)) {
            if ($previousMejaId && (int) $previousMejaId !== (int) $reservasi->id_meja) {
                $this->releaseTableWhenUnusedById($previousMejaId, $reservasi->getKey());
            }

            return;
        }

        $meja = Meja::find($reservasi->id_meja);

        if (! $meja || $meja->status_meja !== 'active') {
            throw ValidationException::withMessages([
                'meja_id' => 'Meja tidak tersedia.',
            ]);
        }

        if ((int) $meja->capacity < (int) $reservasi->jml_orang) {
            throw ValidationException::withMessages([
                'meja_id' => 'Kapasitas meja tidak cukup untuk jumlah orang.',
            ]);
        }

        $alreadyConfirmedOnThisTable =
            $previousStatus === 'dikonfirmasi' &&
            (int) $previousMejaId === (int) $meja->getKey();

        if ((int) $meja->used_seats > 0 && ! $alreadyConfirmedOnThisTable) {
            throw ValidationException::withMessages([
                'meja_id' => 'Meja sedang terpakai.',
            ]);
        }

        $meja->update([
            'used_seats' => min((int) $reservasi->jml_orang, (int) $meja->capacity),
        ]);
    }

    private function releaseTableWhenUnused(Reservasi $reservasi): void
    {
        if (! $reservasi->id_meja) {
            return;
        }

        $this->releaseTableWhenUnusedById($reservasi->id_meja, $reservasi->getKey());
    }

    private function releaseTableWhenUnusedById(int $mejaId, ?int $ignoredReservasiId = null): void
    {
        $hasConfirmedReservation = Reservasi::query()
            ->where('id_meja', $mejaId)
            ->where('status_reservasi', 'dikonfirmasi')
            ->whereDate('tgl_reservasi', Carbon::now()->toDateString())
            ->when($ignoredReservasiId, fn ($query) => $query->where('id_reservasi', '!=', $ignoredReservasiId))
            ->exists();

        $hasActiveOrder = Pesanan::query()
            ->where('id_meja', $mejaId)
            ->where('status_pesanan', 'diproses')
            ->exists();

        if ($hasConfirmedReservation || $hasActiveOrder) {
            return;
        }

        Meja::query()
            ->whereKey($mejaId)
            ->where('status_meja', 'active')
            ->update(['used_seats' => 0]);
    }

    private function ensureNoScheduleConflict(
        int $mejaId,
        ?string $reservationDate,
        ?string $reservationTime,
        ?int $ignoredReservasiId = null
    ): void {
        if (! $reservationDate) {
            return;
        }

        $conflictQuery = Reservasi::query()
            ->where('id_meja', $mejaId)
            ->whereDate('tgl_reservasi', Carbon::parse($reservationDate)->toDateString())
            ->whereIn('status_reservasi', ['menunggu_konfirmasi', 'dikonfirmasi'])
            ->when($ignoredReservasiId, fn ($query) => $query->where('id_reservasi', '!=', $ignoredReservasiId));

        $hasConflict = $conflictQuery->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'meja_id' => 'Meja sudah dipesan pada tanggal tersebut. Admin harus menandai reservasi selesai/dibatalkan dulu sebelum meja bisa dipakai reservasi lain.',
            ]);
        }
    }

    private function isReservationForToday(?string $reservationDate): bool
    {
        if (! $reservationDate) {
            return true;
        }

        return Carbon::parse($reservationDate)->isSameDay(Carbon::now());
    }
}
