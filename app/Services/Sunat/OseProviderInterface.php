<?php

namespace App\Services\Sunat;

use App\Models\Comprobante;

interface OseProviderInterface
{
    /** @param  array<string, mixed>  $cfg */
    public function emitir(Comprobante $comprobante, string $xml, array $cfg): OseResult;

    /** @param  array<string, mixed>  $cfg */
    public function anular(Comprobante $comprobante, array $cfg): OseResult;
}
