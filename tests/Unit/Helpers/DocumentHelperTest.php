<?php

it('formats cpf and cnpj documents for display', function () {
    expect(formatCnpjCpf('12345678901'))->toBe('123.456.789-01')
        ->and(formatCnpjCpf('12345678000195'))->toBe('12.345.678/0001-95')
        ->and(formatCnpjCpf(null))->toBe('');
});
