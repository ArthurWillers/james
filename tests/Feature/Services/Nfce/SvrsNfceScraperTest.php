<?php

use App\Services\Nfce\Data\NfceSource;
use App\Services\Nfce\Exceptions\NfceInvoiceParsingException;
use App\Services\Nfce\Exceptions\NfcePortalUnavailableException;
use App\Services\Nfce\NfceScraperResolver;
use App\Services\Nfce\Scrapers\SvrsNfceScraper;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('it extracts normalized invoice data from the svrs portal', function () {
    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response(svrsFixture(), 200, ['Content-Type' => 'text/html']),
    ]);

    $invoice = app(SvrsNfceScraper::class)->scrape(svrsNfceSource());

    expect($invoice->issuer)->toBe('EMPRESA FICTICIA DE TESTE LTDA')
        ->and($invoice->issuedAt->format('Y-m-d H:i:s'))->toBe('2026-07-14 15:25:55')
        ->and($invoice->totalAmount)->toBe('125.69')
        ->and($invoice->items)->toHaveCount(7)
        ->and($invoice->items[0]->description)->toBe('ERVA MATE TRADICIONAL 1KG')
        ->and($invoice->items[0]->quantity)->toBe('2')
        ->and($invoice->items[0]->unitPrice)->toBe('14.59')
        ->and($invoice->items[2]->quantity)->toBe('0.416')
        ->and($invoice->items[6]->description)->toBe('Desconto da NFC-e')
        ->and($invoice->items[6]->quantity)->toBe('1')
        ->and($invoice->items[6]->unitPrice)->toBe('-16.29');

    Http::assertSent(fn (Request $request): bool => $request->url() === svrsNfceSource()->requestUrl
        && $request->hasHeader('User-Agent', config('app.name').'/NFC-e Importer'));
});

test('it is registered in the scraper resolver', function () {
    $scraper = app(NfceScraperResolver::class)->resolve(svrsNfceSource());

    expect($scraper)->toBeInstanceOf(SvrsNfceScraper::class);
});

test('it retries connection failures before reporting portal unavailability', function () {
    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::failedConnection(),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfcePortalUnavailableException::class);

    Http::assertSentCount(4);
});

test('it retries server errors before reporting portal unavailability', function () {
    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response('Unavailable', 503),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfcePortalUnavailableException::class);

    Http::assertSentCount(4);
});

test('it does not retry non-transient responses', function (int $status) {
    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response('', $status),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfcePortalUnavailableException::class);

    Http::assertSentCount(1);
})->with([302, 404]);

test('it rejects incomplete portal html', function () {
    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response('<html><body>Invalid NFC-e</body></html>'),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfceInvoiceParsingException::class);
});

test('it accepts an invoice without discounts', function () {
    $html = str_replace(
        [
            '        <div id="linhaTotal"><label>Descontos R$:</label><span class="totalNumb">16,29</span></div>'.PHP_EOL,
            '<label>Valor a pagar R$:</label><span class="totalNumb txtMax">125,69</span>',
        ],
        [
            '',
            '<label>Valor a pagar R$:</label><span class="totalNumb txtMax">141,98</span>',
        ],
        svrsFixture(),
    );

    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response($html),
    ]);

    $invoice = app(SvrsNfceScraper::class)->scrape(svrsNfceSource());

    expect($invoice->totalAmount)->toBe('141.98')
        ->and($invoice->items)->toHaveCount(6);
});

test('it rejects inconsistent invoice totals', function () {
    $html = str_replace(
        '<label>Valor a pagar R$:</label><span class="totalNumb txtMax">125,69</span>',
        '<label>Valor a pagar R$:</label><span class="totalNumb txtMax">125,70</span>',
        svrsFixture(),
    );

    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response($html),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfceInvoiceParsingException::class);
});

test('it rejects a response for a different access key', function () {
    $html = str_replace('4311 1111', '4211 1111', svrsFixture());

    Http::fake([
        'dfe-portal.svrs.rs.gov.br/*' => Http::response($html),
    ]);

    expect(fn () => app(SvrsNfceScraper::class)->scrape(svrsNfceSource()))
        ->toThrow(NfceInvoiceParsingException::class);
});

function svrsNfceSource(): NfceSource
{
    return new NfceSource(
        requestUrl: 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43111111111111111111111111111111111111111111%7C3%7C1',
        provider: 'svrs',
        accessKey: '43111111111111111111111111111111111111111111',
        uf: 'RS',
        sourceEndpoint: 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce',
    );
}

function svrsFixture(): string
{
    return (string) file_get_contents(base_path('tests/Fixtures/Nfce/svrs-invoice.html'));
}
