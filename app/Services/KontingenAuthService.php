<?php

namespace App\Services;

use App\Models\KontingenModel;
use Config\Services;

class KontingenAuthService
{
    public function attempt(string $email, string $password): bool
    {
        $throttler = Services::throttler();
        $throttleKey = 'kontingen-login:' . sha1(strtolower(trim($email)) . '|' . service('request')->getIPAddress());

        if (! $throttler->check($throttleKey, 5, MINUTE)) {
            throw new \RuntimeException('Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.');
        }

        $kontingen = (new KontingenModel())
            ->where('email_kontingen', $email)
            ->first();

        if (! $kontingen || ! isset($kontingen->password)) {
            return false;
        }

        if (! password_verify($password, (string) $kontingen->password)) {
            return false;
        }

        session()->set([
            'level'          => 'kontingen',
            'id_kontingen'   => $kontingen->id_kontingen,
            'nama_kontingen' => $kontingen->nama_kontingen,
            'perguruan'      => $kontingen->perguruan,
            'status_data'    => $kontingen->status_data,
        ]);

        return true;
    }

    public function logout(): void
    {
        session()->destroy();
    }
}
