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
    protected $appends = ['id', 'receipt_token'];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function getReceiptTokenAttribute(): string
    {
        return $this->makeReceiptToken();
    }

    public function hasValidReceiptToken(?string $token): bool
    {
        return is_string($token) && hash_equals($this->makeReceiptToken(), $token);
    }

    private function makeReceiptToken(): string
    {
        $createdAt = $this->created_at
            ? $this->created_at->timestamp
            : (string) $this->tgl_pesanan;

        return hash_hmac(
            'sha256',
            implode('|', [$this->getKey(), $createdAt]),
            (string) config('app.key')
        );
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
