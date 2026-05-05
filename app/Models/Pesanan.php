<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Models\DetailPesanan;
use App\Models\Review;

class Pesanan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function detail_pesanans()
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}