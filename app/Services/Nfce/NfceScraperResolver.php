<?php

namespace App\Services\Nfce;

use App\Services\Nfce\Contracts\NfceScraperInterface;
use App\Services\Nfce\Data\NfceSource;
use App\Services\Nfce\Exceptions\UnsupportedNfceProviderException;

final class NfceScraperResolver
{
    /**
     * @param  iterable<NfceScraperInterface>  $scrapers
     */
    public function __construct(private readonly iterable $scrapers) {}

    public function resolve(NfceSource $source): NfceScraperInterface
    {
        foreach ($this->scrapers as $scraper) {
            if ($scraper->provider() === $source->provider) {
                return $scraper;
            }
        }

        throw new UnsupportedNfceProviderException('Não há um scraper disponível para este portal.');
    }
}
