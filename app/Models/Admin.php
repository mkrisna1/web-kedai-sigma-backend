<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservasi;
use App\Models\Review;

class Admin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}