<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservasiStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_reservasi' => 'required|in:menunggu_konfirmasi,dikonfirmasi,selesai,dibatalkan',
            'meja_id' => 'nullable|exists:mejas,id_meja',
        ];
    }
}
