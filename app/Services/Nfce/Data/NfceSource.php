<?php

namespace App\Services\Nfce\Data;

final readonly class NfceSource
{
    public function __construct(
        public string $requestUrl,
        public string $provider,
        public string $accessKey,
        public ?string $uf,
        public string $sourceEndpoint,
        public string $requestParameterSuffix = '',
    ) {}
}
