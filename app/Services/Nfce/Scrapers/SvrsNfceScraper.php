<?php

namespace App\Services\Nfce\Scrapers;

use App\Services\Nfce\Contracts\NfceScraperInterface;
use App\Services\Nfce\Data\NfceInvoiceData;
use App\Services\Nfce\Data\NfceInvoiceItemData;
use App\Services\Nfce\Data\NfceSource;
use App\Services\Nfce\Exceptions\NfceInvoiceParsingException;
use App\Services\Nfce\Exceptions\NfcePortalUnavailableException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class SvrsNfceScraper implements NfceScraperInterface
{
    /**
     * @param  array{timeout: int, connect_timeout: int, retry_delays: list<int>}  $httpConfig
     */
    public function __construct(private readonly array $httpConfig) {}

    public function provider(): string
    {
        return 'svrs';
    }

    public function scrape(NfceSource $source): NfceInvoiceData
    {
        $response = $this->request($source);

        try {
            return $this->parse($response->body(), $source);
        } catch (NfceInvoiceParsingException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new NfceInvoiceParsingException(
                'Não foi possível interpretar os dados retornados pelo portal da NFC-e.',
                previous: $exception,
            );
        }
    }

    private function request(NfceSource $source): Response
    {
        try {
            $response = Http::accept('text/html, application/xhtml+xml')
                ->withUserAgent(config('app.name').'/NFC-e Importer')
                ->timeout($this->httpConfig['timeout'])
                ->connectTimeout($this->httpConfig['connect_timeout'])
                ->withoutRedirecting()
                ->retry(
                    times: $this->httpConfig['retry_delays'],
                    when: fn (?Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError()),
                    throw: false,
                )
                ->get($source->requestUrl);
        } catch (ConnectionException|RequestException $exception) {
            throw new NfcePortalUnavailableException(
                'O portal da NFC-e está indisponível no momento.',
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            throw new NfcePortalUnavailableException(
                "O portal da NFC-e respondeu com o status HTTP {$response->status()}.",
            );
        }

        return $response;
    }

    private function parse(string $html, NfceSource $source): NfceInvoiceData
    {
        $crawler = new Crawler($html);
        $issuer = $this->requiredText($crawler, '//*[@id="u20"]', 'emitente');
        $accessKey = preg_replace(
            '/\D/',
            '',
            $this->requiredText($crawler, '//*[@id="infos"]//span[contains(@class, "chave")]', 'chave de acesso'),
        );

        if ($accessKey !== $source->accessKey) {
            throw new NfceInvoiceParsingException('A chave de acesso retornada pelo portal não corresponde à NFC-e solicitada.');
        }

        $issuedAt = $this->parseIssuedAt(
            $this->requiredText(
                $crawler,
                '//*[@id="infos"]//li[contains(normalize-space(.), "Emissão:")][1]',
                'data de emissão',
            ),
        );
        [$items, $itemsTotalInCents] = $this->parseItems($crawler);
        $grossTotal = $this->parseCurrencyInCents($this->requiredText(
            $crawler,
            '//*[@id="totalNota"]/*[@id="linhaTotal"][label[contains(normalize-space(.), "Valor total R$")]]/span',
            'valor total',
        ));
        $discountText = $this->optionalText(
            $crawler,
            '//*[@id="totalNota"]/*[@id="linhaTotal"][label[contains(normalize-space(.), "Descontos R$")]]/span',
        );
        $discount = $discountText === null ? 0 : $this->parseCurrencyInCents($discountText);
        $netTotal = $this->parseCurrencyInCents($this->requiredText(
            $crawler,
            '//*[@id="totalNota"]/*[@id="linhaTotal"][label[contains(normalize-space(.), "Valor a pagar R$")]]/span',
            'valor a pagar',
        ));

        if ($itemsTotalInCents !== $grossTotal || $grossTotal - $discount !== $netTotal) {
            throw new NfceInvoiceParsingException('Os totais retornados pelo portal da NFC-e são inconsistentes.');
        }

        if ($discount > 0) {
            $items[] = new NfceInvoiceItemData(
                description: 'Desconto da NFC-e',
                quantity: '1',
                unitPrice: '-'.$this->formatCents($discount),
            );
        }

        return new NfceInvoiceData(
            issuer: $issuer,
            issuedAt: $issuedAt,
            totalAmount: $this->formatCents($netTotal),
            items: $items,
        );
    }

    private function parseIssuedAt(string $value): CarbonImmutable
    {
        if (preg_match('/Emissão:\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})/u', $value, $matches) !== 1) {
            throw new NfceInvoiceParsingException('A data de emissão retornada pelo portal da NFC-e é inválida.');
        }

        $issuedAt = CarbonImmutable::createFromFormat('!d/m/Y H:i:s', $matches[1], config('app.timezone'));

        if ($issuedAt === false || CarbonImmutable::getLastErrors() !== false) {
            throw new NfceInvoiceParsingException('A data de emissão retornada pelo portal da NFC-e é inválida.');
        }

        return $issuedAt;
    }

    /**
     * @return array{0: list<NfceInvoiceItemData>, 1: int}
     */
    private function parseItems(Crawler $crawler): array
    {
        $rows = $crawler->filterXPath('//*[@id="tabResult"]//tr');

        if ($rows->count() === 0) {
            throw new NfceInvoiceParsingException('Nenhum item foi encontrado na NFC-e.');
        }

        $items = [];
        $itemsTotalInCents = 0;

        $rows->each(function (Crawler $row) use (&$items, &$itemsTotalInCents): void {
            $items[] = new NfceInvoiceItemData(
                description: $this->requiredText($row, './/td[1]//span[contains(@class, "txtTit")]', 'descrição do item'),
                quantity: $this->parseDecimal($this->requiredText($row, './/td[1]//span[contains(@class, "Rqtd")]', 'quantidade do item')),
                unitPrice: $this->parseDecimal($this->requiredText($row, './/td[1]//span[contains(@class, "RvlUnit")]', 'valor unitário do item')),
            );
            $itemsTotalInCents += $this->parseCurrencyInCents(
                $this->requiredText($row, './/td[2]//span[contains(@class, "valor")]', 'valor total do item'),
            );
        });

        return [$items, $itemsTotalInCents];
    }

    private function requiredText(Crawler $crawler, string $xpath, string $field): string
    {
        $nodes = $crawler->filterXPath($xpath);

        if ($nodes->count() === 0) {
            throw new NfceInvoiceParsingException("O portal da NFC-e não retornou {$field}.");
        }

        $value = trim((string) preg_replace('/\s+/u', ' ', $nodes->first()->text('')));

        if ($value === '') {
            throw new NfceInvoiceParsingException("O portal da NFC-e retornou {$field} vazio.");
        }

        return $value;
    }

    private function optionalText(Crawler $crawler, string $xpath): ?string
    {
        $nodes = $crawler->filterXPath($xpath);

        if ($nodes->count() === 0) {
            return null;
        }

        $value = trim((string) preg_replace('/\s+/u', ' ', $nodes->first()->text('')));

        return $value;
    }

    private function parseCurrencyInCents(string $value): int
    {
        $decimal = $this->parseDecimal($value);
        $isNegative = str_starts_with($decimal, '-');
        $unsignedDecimal = ltrim($decimal, '-');
        [$integer, $fraction] = array_pad(explode('.', $unsignedDecimal, 2), 2, '');

        if (strlen($fraction) > 2 && trim(substr($fraction, 2), '0') !== '') {
            throw new NfceInvoiceParsingException('O portal da NFC-e retornou um valor monetário inválido.');
        }

        $cents = ((int) $integer * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return $isNegative ? -$cents : $cents;
    }

    private function parseDecimal(string $value): string
    {
        $value = str_replace(["\u{00A0}", ' '], '', $value);

        if (preg_match('/-?\d[\d.,]*/u', $value, $matches) !== 1) {
            throw new NfceInvoiceParsingException('O portal da NFC-e retornou um valor numérico inválido.');
        }

        $value = $matches[0];

        if (preg_match('/^-?(?:\d{1,3}(?:\.\d{3})+|\d+)(?:,\d+)?$/', $value) === 1) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        } elseif (preg_match('/^-?\d+(?:\.\d+)?$/', $value) !== 1) {
            throw new NfceInvoiceParsingException('O portal da NFC-e retornou um valor numérico inválido.');
        }

        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $isNegative = str_starts_with($integer, '-');
        $integer = ltrim($integer, '-0');
        $integer = $integer === '' ? '0' : $integer;
        $fraction = rtrim($fraction, '0');
        $normalized = $integer.($fraction === '' ? '' : '.'.$fraction);

        return $isNegative && $normalized !== '0' ? '-'.$normalized : $normalized;
    }

    private function formatCents(int $cents): string
    {
        $absoluteCents = abs($cents);
        $formatted = intdiv($absoluteCents, 100).'.'.str_pad((string) ($absoluteCents % 100), 2, '0', STR_PAD_LEFT);

        return $cents < 0 ? '-'.$formatted : $formatted;
    }
}
