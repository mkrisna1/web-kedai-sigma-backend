<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\KategoriProduk;
use App\Models\DetailPesanan;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

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
