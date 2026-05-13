<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; 
use App\Models\Reservasi;
use App\Models\Review;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory; 

    protected $guarded = ['id'];
    protected $hidden = ['password'];

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}