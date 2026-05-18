<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $data): array|false
    {
        $username = trim($data['username']);

        $admin = Admin::whereRaw('LOWER(username) = ?', [strtolower($username)])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return false;
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }

    public function logout($request): void
    {
        $request->user()?->currentAccessToken()?->delete();
    }
}
