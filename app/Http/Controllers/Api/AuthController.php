<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi inputan dari frontend
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Cari admin berdasarkan username
        $admin = Admin::where('username', $request->username)->first();

        // 3. Cek apakah admin ada dan passwordnya cocok
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah!'
            ], 401);
        }

        // 4. Buat Token
        $token = $admin->createToken('admin_token')->plainTextToken;

        // 5. Kembalikan response sukses beserta token
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $admin,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }
}