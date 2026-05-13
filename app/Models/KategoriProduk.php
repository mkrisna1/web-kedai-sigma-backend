<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class KategoriProduk extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}