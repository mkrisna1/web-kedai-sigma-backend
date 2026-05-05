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

    protected $guarded = ['id'];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }
}