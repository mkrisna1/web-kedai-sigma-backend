<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pesanan;
use App\Models\Admin;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}