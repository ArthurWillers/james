<?php

namespace App\Services\Nfce\Data;

use Carbon\CarbonImmutable;

final readonly class NfceInvoiceData
{
    /**
     * @param  list<NfceInvoiceItemData>  $items
     */
    public function __construct(
        public string $issuer,
        public ?string $issuerDocument,
        public CarbonImmutable $issuedAt,
        public string $totalAmount,
        public array $items,
    ) {}
}
