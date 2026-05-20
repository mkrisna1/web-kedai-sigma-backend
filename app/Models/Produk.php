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

    protected $primaryKey = 'id_produk';
    protected $guarded = ['id_produk'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'id_kategori', 'id_kategori');
    }

    public function detail_pesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'id_produk', 'id_produk');
    }
}
