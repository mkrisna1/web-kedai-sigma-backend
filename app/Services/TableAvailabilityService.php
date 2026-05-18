<?php

namespace App\Services;

use App\Models\Meja;
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
    }
}
