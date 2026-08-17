<?php

use App\Services\Nfce\Exceptions\InvalidNfceUrlException;
use App\Services\Nfce\Exceptions\UnsupportedNfceProviderException;
use App\Services\Nfce\NfceSourceResolver;

function nfceSourceResolver(): NfceSourceResolver
{
    return app(NfceSourceResolver::class);
}

it('resolves and normalizes a valid SVRS NFC-e URL', function () {
    $source = nfceSourceResolver()->resolve(
        'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935%7C3%7C1'
    );

    expect($source->provider)->toBe('svrs')
        ->and($source->accessKey)->toBe('43260702247794000207650100003711221171005935')
        ->and($source->uf)->toBe('RS')
        ->and($source->sourceEndpoint)->toBe('https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce')
        ->and($source->requestParameterSuffix)->toBe('|3|1')
        ->and($source->requestUrl)
        ->toBe('https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935%7C3%7C1');
});

it('supports other UFs and URL path casing served by SVRS', function () {
    $source = nfceSourceResolver()->resolve(
        'https://dfe-portal.svrs.rs.gov.br/dfe/qrcodenfce?p=42260702247794000207650100003711221171005935%7C3%7C1'
    );

    expect($source->provider)->toBe('svrs')
        ->and($source->uf)->toBe('SC')
        ->and($source->sourceEndpoint)->toBe('https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce');
});

it('returns null when the access key UF code is unknown', function () {
    $source = nfceSourceResolver()->resolve(
        'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=99260702247794000207650100003711221171005935%7C3%7C1'
    );

    expect($source->uf)->toBeNull();
});

it('rejects unsafe or malformed NFC-e URLs', function (string $url) {
    expect(fn () => nfceSourceResolver()->resolve($url))
        ->toThrow(InvalidNfceUrlException::class);
})->with([
    'non-HTTPS scheme' => 'http://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935',
    'embedded credentials' => 'https://user:secret@dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935',
    'custom port' => 'https://dfe-portal.svrs.rs.gov.br:8443/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935',
    'missing query data' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce',
    'short access key' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=432607',
    'non-numeric access key' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/QrCodeNFce?p=AB260702247794000207650100003711221171005935',
]);

it('rejects unsupported hosts and paths', function (string $url) {
    expect(fn () => nfceSourceResolver()->resolve($url))
        ->toThrow(UnsupportedNfceProviderException::class);
})->with([
    'lookalike host' => 'https://dfe-portal.svrs.rs.gov.br.evil.test/Dfe/QrCodeNFce?p=43260702247794000207650100003711221171005935',
    'unsupported path' => 'https://dfe-portal.svrs.rs.gov.br/Dfe/Other?p=43260702247794000207650100003711221171005935',
]);
