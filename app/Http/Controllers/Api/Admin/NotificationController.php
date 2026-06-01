<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
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
        Pesanan::where('is_notif_read', false)->update(['is_notif_read' => true]);
        Reservasi::where('is_notif_read', false)->update(['is_notif_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai telah dibaca.',
        ]);
    }
}
