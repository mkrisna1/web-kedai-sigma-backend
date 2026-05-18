<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Meja;
use App\Models\Admin;
use App\Models\Pesanan;

class Reservasi extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_reservasi';
    protected $guarded = ['id_reservasi'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_reservasi', 'id_reservasi');
    }
}
