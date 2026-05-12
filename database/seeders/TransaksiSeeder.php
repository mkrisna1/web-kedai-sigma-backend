<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservasi;
use App\Models\Pesanan;
use App\Models\DetailPesanan;

class TransaksiSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Dummy Reservasi (Meja 1)
        Reservasi::create([
            'meja_id' => 1,
            'admin_id' => 1, // Di-acc oleh Super Admin
            'nama_reservasi' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'tgl_reservasi' => date('Y-m-d'),
            'jam_reservasi' => '19:00:00',
            'jml_orang' => 4,
            'status_reservasi' => 'dikonfirmasi',
            'catatan_reservasi' => 'Minta meja dekat jendela ya.'
        ]);

        // 2. Buat Dummy Pesanan (Meja 2, Walk-in / bukan reservasi)
        $pesanan = Pesanan::create([
            'meja_id' => 2,
            'tgl_pesanan' => now(),
            'status_pesanan' => 'selesai',
            'status_pembayaran' => 'lunas',
            'tipe_pesanan' => 'dine_in',
            'total_harga' => 40000, // (25.000 Latte + 15.000 Kentang)
            'catatan_pesanan' => 'Latte-nya less sugar ya.'
        ]);

        // 3. Buat Dummy Detail Pesanan (Isi dari pesanan di atas)
        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => 1, // Caffe Latte
            'jumlah_item' => 1,
            'opsi_varian' => 'Hot, Less Sugar',
            'subtotal' => 25000
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => 2, // Kentang Goreng
            'jumlah_item' => 1,
            'opsi_varian' => 'Pedas',
            'subtotal' => 15000
        ]);

    }
}