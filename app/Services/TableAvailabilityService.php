<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class TableAvailabilityService
{
    public function releaseStaleOccupiedTables(): void
    {
        if (
            ! Schema::hasColumn('mejas', 'used_seats') ||
            ! Schema::hasColumn('mejas', 'updated_at')
        ) {
            return;
        }

        $startOfToday = Carbon::now()->startOfDay();

        Meja::query()
            ->where('status_meja', 'active')
            ->where('used_seats', '>', 0)
            ->where('updated_at', '<', $startOfToday)
            ->update(['used_seats' => 0]);

        Reservasi::with('meja')
            ->whereDate('tgl_reservasi', $startOfToday->toDateString())
            ->where('status_reservasi', 'dikonfirmasi')
            ->get()
            ->each(function (Reservasi $reservasi) {
                $meja = $reservasi->meja;

                if (! $meja || $meja->status_meja !== 'active') {
                    return;
                }

                $meja->update([
                    'used_seats' => min((int) $reservasi->jml_orang, (int) $meja->capacity),
                ]);
            });
    }
}
