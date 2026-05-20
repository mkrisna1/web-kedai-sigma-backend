<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class KategoriProduk extends Model
{
    use HasFactory;

    protected $table = 'kategori_produks';
    protected $primaryKey = 'id_kategori';
    protected $guarded = ['id_kategori'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function produks()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}
