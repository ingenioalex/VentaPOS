<?php

namespace App\Services\Sunat;

class OseProviderFactory
{
    public function __construct(
        private GenericJsonOseProvider $generico,
        private NubefactOseProvider $nubefact,
    ) {}

    /** @param  array<string, mixed>  $cfg */
    public function resolver(array $cfg): OseProviderInterface
    {
        $proveedor = $cfg['proveedor_ose'] ?? 'generico';

        return match ($proveedor) {
            'nubefact' => $this->nubefact,
            default => $this->generico,
        };
    }
}
