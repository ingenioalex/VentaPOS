<?php

namespace App\Services\Sunat;

use Illuminate\Support\Facades\Crypt;

class SunatCredentialCipher
{
    private const PREFIX = 'enc:';

    public function seal(string $plain): string
    {
        if ($plain === '') {
            return '';
        }

        return self::PREFIX.Crypt::encryptString($plain);
    }

    public function unseal(string $stored): string
    {
        if ($stored === '') {
            return '';
        }

        if (! str_starts_with($stored, self::PREFIX)) {
            return $stored;
        }

        try {
            return Crypt::decryptString(substr($stored, strlen(self::PREFIX)));
        } catch (\Throwable) {
            return '';
        }
    }
}
