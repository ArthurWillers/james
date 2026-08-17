<?php

use App\Services\Nfce\Contracts\NfceScraperInterface;
use App\Services\Nfce\Data\NfceInvoiceData;
use App\Services\Nfce\Data\NfceSource;
use App\Services\Nfce\Exceptions\UnsupportedNfceProviderException;
use App\Services\Nfce\NfceScraperResolver;

it('resolves the scraper registered for the source provider', function () {
    $scraper = new class implements NfceScraperInterface
    {
        public function provider(): string
        {
            return 'svrs';
        }

        public function scrape(NfceSource $source): NfceInvoiceData
        {
            throw new LogicException('Not needed by this test.');
        }
    };

    $source = new NfceSource(
        requestUrl: 'https://example.com/invoice',
        provider: 'svrs',
        accessKey: str_repeat('1', 44),
        uf: 'RS',
        sourceEndpoint: 'https://example.com/invoice',
    );

    expect((new NfceScraperResolver([$scraper]))->resolve($source))->toBe($scraper);
});

it('rejects providers without a registered scraper', function () {
    $source = new NfceSource(
        requestUrl: 'https://example.com/invoice',
        provider: 'unknown',
        accessKey: str_repeat('1', 44),
        uf: null,
        sourceEndpoint: 'https://example.com/invoice',
    );

    expect(fn () => (new NfceScraperResolver([]))->resolve($source))
        ->toThrow(UnsupportedNfceProviderException::class);
});
