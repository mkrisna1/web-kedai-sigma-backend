<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

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
            'tgl_reservasi'  => 'required|date|after_or_equal:today',
            'jam_reservasi'  => 'required|date_format:H:i',
            'jml_orang'      => 'required|integer|min:1|max:20',
            'catatan_reservasi' => 'nullable|string',
        ];
    }
}