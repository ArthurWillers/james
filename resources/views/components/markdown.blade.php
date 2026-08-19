@props(['content'])

@php
    $applicationHost = parse_url((string) config('app.url'), PHP_URL_HOST);
    $internalHosts = is_string($applicationHost) ? $applicationHost : '';
@endphp

{!! str((string) $content)->markdown([
    'allow_unsafe_links' => false,
    'external_link' => [
        'internal_hosts' => $internalHosts,
        'open_in_new_window' => true,
    ],
], [new \League\CommonMark\Extension\ExternalLink\ExternalLinkExtension()]) !!}
