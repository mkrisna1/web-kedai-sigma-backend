<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class StoreReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_reservasi' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:15',
            'meja_id'         => 'required|integer|exists:mejas,id_meja',
            'tgl_reservasi'  => 'required|date|after_or_equal:today',
            'jam_reservasi'  => 'required|date_format:H:i',
            'jml_orang'      => 'required|integer|min:1|max:8',
            'catatan_reservasi' => 'nullable|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dateValue = $this->input('tgl_reservasi');
            $timeValue = $this->input('jam_reservasi');

            if (! $dateValue || ! $timeValue || ! preg_match('/^\d{2}:\d{2}$/', (string) $timeValue)) {
                return;
            }

            try {
                $date = Carbon::parse($dateValue)->startOfDay();
                $reservationAt = Carbon::parse($date->toDateString() . ' ' . $timeValue);
            } catch (\Throwable) {
                return;
            }

            $today = Carbon::today();
            $lastAllowedDate = $today->copy()->addDays(14);
            $openingTime = Carbon::parse($date->toDateString() . ' 16:00');
            $closingTime = Carbon::parse($date->toDateString() . ' 23:30');

            if ($date->greaterThan($lastAllowedDate)) {
                $validator->errors()->add(
                    'tgl_reservasi',
                    'Reservasi hanya bisa dibuat maksimal 14 hari ke depan.'
                );
            }

            if ($reservationAt->lt($openingTime) || $reservationAt->gt($closingTime)) {
                $validator->errors()->add(
                    'jam_reservasi',
                    'Jam reservasi harus berada di antara 16:00 sampai 23:30.'
                );
            }

            if ($date->isSameDay($today) && $reservationAt->lt(now()->addHours(2))) {
                $validator->errors()->add(
                    'jam_reservasi',
                    'Reservasi hari ini minimal 2 jam dari waktu sekarang.'
                );
            }
        });
    }
}
