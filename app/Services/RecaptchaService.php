<?php

namespace App\Services;

class RecaptchaService
{
    public function isConfigured(): bool
    {
        return trim((string) env('recaptcha.secretKey')) !== '';
    }

    public function siteKey(): string
    {
        return trim((string) env('recaptcha.siteKey', ''));
    }

    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        $secret = trim((string) env('recaptcha.secretKey'));
        if ($secret === '') {
            return true;
        }

        if (! $token) {
            return false;
        }

        $client = service('curlrequest', [
            'timeout' => 8,
        ]);

        try {
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ],
            ]);

            $json = json_decode((string) $response->getBody(), true);
        } catch (\Throwable) {
            return false;
        }

        return (bool) ($json['success'] ?? false);
    }
}
