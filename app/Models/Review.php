<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class Review extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id_review';
    protected $guarded = ['id_review'];
    protected $appends = ['id', 'nama_pelanggan'];
    protected $casts = [
        'foto_review' => 'array',
        'likes_count' => 'integer',
    ];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function getNamaPelangganAttribute()
    {
        return $this->attributes['nama_reviewer'] ?? null;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

}
