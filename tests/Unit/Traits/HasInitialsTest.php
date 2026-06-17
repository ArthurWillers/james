<?php

use App\Traits\HasInitials;

class HasInitialsDummy
{
    use HasInitials;

    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

it('retorna string vazia quando nome é nulo ou vazio', function () {
    $dummy = new HasInitialsDummy('');
    expect($dummy->initials())->toBe('');

    $dummy = new HasInitialsDummy(null);
    expect($dummy->initials())->toBe('');
});

it('retorna apenas a primeira letra quando o nome tem apenas uma palavra', function () {
    $dummy = new HasInitialsDummy('Arthur');
    expect($dummy->initials())->toBe('A');

    $dummy = new HasInitialsDummy('Willers');
    expect($dummy->initials())->toBe('W');
});

it('retorna a primeira letra da primeira e última palavra', function () {
    $dummy = new HasInitialsDummy('Arthur Willers');
    expect($dummy->initials())->toBe('AW');

    $dummy = new HasInitialsDummy('Arthur Silva Willers');
    expect($dummy->initials())->toBe('AW');
});

it('ignora espaços extras entre as palavras', function () {
    $dummy = new HasInitialsDummy('  Arthur     Willers  ');
    expect($dummy->initials())->toBe('AW');
});

it('lida corretamente com caracteres acentuados', function () {
    $dummy = new HasInitialsDummy('Ángel Di María');
    expect($dummy->initials())->toBe('ÁM');

    $dummy = new HasInitialsDummy('Éverton Ribeiro');
    expect($dummy->initials())->toBe('ÉR');
});
