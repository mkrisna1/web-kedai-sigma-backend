<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriProduk;
use App\Models\DetailPesanan;

class Produk extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }

    public function detail_pesanans()
    {
        return $this->hasMany(DetailPesanan::class);
    }
}