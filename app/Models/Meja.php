<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservasi;
use App\Models\Pesanan;

class Meja extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }
}