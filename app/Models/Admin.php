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

    protected $primaryKey = 'id_admin';
    protected $guarded = ['id_admin'];
    protected $appends = ['id'];
    protected $hidden = ['password'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class, 'id_admin', 'id_admin');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_admin', 'id_admin');
    }
}
