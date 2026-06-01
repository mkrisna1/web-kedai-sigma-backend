<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $orders = Pesanan::query()
            ->withCount('detail_pesanans')
            ->with('meja:id_meja,nomor_meja')
            ->where('status_pesanan', 'menunggu_konfirmasi')
            ->where('is_notif_read', false)
            ->latest('tgl_pesanan')
            ->limit(20)
            ->get([
                'id_pesanan',
                'id_meja',
                'tgl_pesanan',
                'created_at',
                'status_pesanan',
                'is_notif_read',
            ]);

        $reservations = Reservasi::query()
            ->where('status_reservasi', 'menunggu_konfirmasi')
            ->where('is_notif_read', false)
            ->latest('created_at')
            ->limit(20)
            ->get([
                'id_reservasi',
                'nama_reservasi',
                'jml_orang',
                'tgl_reservasi',
                'jam_reservasi',
                'status_reservasi',
                'is_notif_read',
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'reservations' => $reservations,
                'total' => $orders->count() + $reservations->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Pesanan,Reservasi',
            'id' => 'required|integer',
        ]);

        if ($data['type'] === 'Pesanan') {
            Pesanan::where('id_pesanan', $data['id'])->update(['is_notif_read' => true]);
        } elseif ($data['type'] === 'Reservasi') {
            Reservasi::where('id_reservasi', $data['id'])->update(['is_notif_read' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai telah dibaca.',
        ]);
    }

    public function markAllAsRead()
    {
        Pesanan::where('status_pesanan', 'menunggu_konfirmasi')
            ->where('is_notif_read', false)
            ->update(['is_notif_read' => true]);
        Reservasi::where('status_reservasi', 'menunggu_konfirmasi')
            ->where('is_notif_read', false)
            ->update(['is_notif_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai telah dibaca.',
        ]);
    }
}
