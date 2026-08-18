<?php

namespace App\Services\Nfce;

use App\Services\Nfce\Data\NfceSource;
use App\Services\Nfce\Exceptions\InvalidNfceUrlException;
use App\Services\Nfce\Exceptions\UnsupportedNfceProviderException;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use League\Uri\Contracts\UriException;

final class NfceSourceResolver
{
    /**
     * @param  list<array{provider: string, hosts: list<string>, paths: list<string>, source_endpoint: string}>  $sources
     * @param  array<string, string>  $ufCodes
     */
    public function __construct(
        private readonly array $sources,
        private readonly array $ufCodes,
    ) {}

    public function resolve(string $url): NfceSource
    {
        $uri = $this->parseUri($url);
        $this->ensureSecureUri($uri);

        $configuredSource = $this->findConfiguredSource($uri);
        $queryParameter = $uri->query()->get('p');

        if (! is_string($queryParameter) || blank($queryParameter)) {
            throw new InvalidNfceUrlException('A URL não contém os dados da NFC-e.');
        }

        $accessKey = trim(Str::before($queryParameter, '|'));

        if (Str::length($accessKey) !== 44 || ! ctype_digit($accessKey)) {
            throw new InvalidNfceUrlException('A chave de acesso da NFC-e deve conter 44 dígitos.');
        }

        $sourceEndpoint = $configuredSource['source_endpoint'];
        $requestUrl = (string) Uri::of($sourceEndpoint)
            ->withQuery(['p' => $queryParameter], merge: false);

        return new NfceSource(
            requestUrl: $requestUrl,
            provider: $configuredSource['provider'],
            accessKey: $accessKey,
            uf: $this->ufCodes[substr($accessKey, 0, 2)] ?? null,
            sourceEndpoint: $sourceEndpoint,
            requestParameterSuffix: Str::after($queryParameter, $accessKey),
        );
    }

    private function parseUri(string $url): Uri
    {
        try {
            return Uri::of(trim($url));
        } catch (UriException) {
            throw new InvalidNfceUrlException('A URL informada não é válida.');
        }
    }

    private function ensureSecureUri(Uri $uri): void
    {
        $hasSecureScheme = Str::lower((string) $uri->scheme()) === 'https';
        $hasCredentials = filled($uri->user()) || filled($uri->password());
        $hasAllowedPort = $uri->port() === null || $uri->port() === 443;

        if (! $hasSecureScheme || $hasCredentials || ! $hasAllowedPort || blank($uri->host())) {
            throw new InvalidNfceUrlException('A URL da NFC-e deve usar HTTPS sem credenciais ou porta personalizada.');
        }
    }

    /**
     * @return array{provider: string, source_endpoint: string}
     */
    private function findConfiguredSource(Uri $uri): array
    {
        $host = Str::lower((string) $uri->host());
        $path = trim($uri->path(), '/');

        foreach ($this->sources as $source) {
            $hosts = array_map(fn (string $configuredHost): string => Str::lower($configuredHost), $source['hosts']);
            $matchedPath = collect($source['paths'])->first(
                fn (string $configuredPath): bool => Str::lower(trim($configuredPath, '/')) === Str::lower($path)
            );

            if (in_array($host, $hosts, true) && is_string($matchedPath)) {
                return [
                    'provider' => $source['provider'],
                    'source_endpoint' => $source['source_endpoint'],
                ];
            }
        }

        throw new UnsupportedNfceProviderException('O portal informado ainda não é suportado.');
    }
}
