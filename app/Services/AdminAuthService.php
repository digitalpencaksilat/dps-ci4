<?php

namespace App\Services;

use App\Models\AdminModel;
use Config\Services;

class AdminAuthService
{
    public function attempt(string $username, string $password): ?string
    {
        $normalizedUsername = trim(strtolower($username));
        $throttler = Services::throttler();
        $throttleKey = 'admin_login_' . sha1($normalizedUsername . '|' . service('request')->getIPAddress());

        if (! $throttler->check($throttleKey, 5, MINUTE)) {
            throw new \RuntimeException('Terlalu banyak percobaan login admin. Coba lagi dalam beberapa menit.');
        }

        $admin = (new AdminModel())
            ->where('LOWER(username)', $normalizedUsername)
            ->first();

        if (! $admin || ! isset($admin->password)) {
            return null;
        }

        if (! password_verify($password, (string) $admin->password)) {
            return null;
        }

        session()->set([
            'level'    => (string) $admin->level,
            'id_admin' => $admin->id_admin,
            'username' => $admin->username,
            'nama'     => $admin->nama,
            'foto'     => $admin->foto,
        ]);

        return (string) $admin->level;
    }

    public function logout(): void
    {
        session()->destroy();
    }

    public function dashboardUrlFor(string $role): string
    {
        return match ($role) {
            'bendahara' => base_url('admin/bendahara/dashboard'),
            'sekretariat' => base_url('admin/sekretariat/dashboard'),
            'super_admin' => base_url('admin/super/dashboard'),
            default => base_url('admin'),
        };
    }
}
