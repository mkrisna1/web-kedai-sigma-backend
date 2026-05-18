<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservasi;
use App\Models\Pesanan;

class Meja extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_meja';
    protected $guarded = ['id_meja'];
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class, 'id_meja', 'id_meja');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_meja', 'id_meja');
    }
}
