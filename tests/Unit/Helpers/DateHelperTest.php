<?php

use App\Helpers\DateHelper;
use Carbon\Carbon;

beforeEach(function () {
    // Como os testes unitários não carregam o Laravel por padrão,
    // forçamos o idioma do Carbon para testar as strings em português.
    Carbon::setLocale('pt_BR');
});

it('formata a data para texto longo em português', function () {
    $date = Carbon::parse('2023-10-15 10:00:00');

    expect(DateHelper::format($date))->toBe('15 de outubro de 2023');
});

it('formata a data para formato curto', function () {
    $date = Carbon::parse('2023-10-15 10:00:00');

    expect(DateHelper::formatShort($date))->toBe('15/10/2023');
});

it('formata a data para formato relativo (diffForHumans)', function () {
    // Simulamos que o "agora" é uma data fixa para o diffForHumans ser determinístico
    Carbon::setTestNow('2023-10-15 10:00:00');

    $date = Carbon::parse('2023-10-13 10:00:00');

    expect(DateHelper::formatRelative($date))->toBe('há 2 dias');

    // Limpamos o mock do "agora" após o teste
    Carbon::setTestNow();
});

it('aceita strings comuns no lugar de instâncias do Carbon', function () {
    expect(DateHelper::formatShort('2023-12-01'))->toBe('01/12/2023');
});
