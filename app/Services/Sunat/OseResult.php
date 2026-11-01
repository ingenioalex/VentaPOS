<?php

namespace App\Services\Sunat;

readonly class OseResult
{
    public function __construct(
        public bool $ok,
        public string $codigo,
        public string $mensaje,
        public ?string $cdr = null,
        public ?string $hash = null,
        public ?string $xml = null,
    ) {}
}
