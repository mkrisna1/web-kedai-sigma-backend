<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Meja;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_detail';
    protected $guarded = ['id_detail'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk')->withTrashed();
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }
}
