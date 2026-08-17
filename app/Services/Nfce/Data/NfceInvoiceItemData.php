<?php

namespace App\Services\Nfce\Data;

final readonly class NfceInvoiceItemData
{
    public function __construct(
        public string $description,
        public string $quantity,
        public string $unitPrice,
    ) {}
}
