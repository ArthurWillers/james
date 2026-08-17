<?php

namespace App\Services\Nfce\Contracts;

use App\Services\Nfce\Data\NfceInvoiceData;
use App\Services\Nfce\Data\NfceSource;

interface NfceScraperInterface
{
    public function provider(): string;

    public function scrape(NfceSource $source): NfceInvoiceData;
}
