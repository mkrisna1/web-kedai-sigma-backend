<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Models\DetailPesanan;

class Pesanan extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_pesanan';
    protected $guarded = ['id_pesanan'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi'); 
    }

    public function detail_pesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

}
