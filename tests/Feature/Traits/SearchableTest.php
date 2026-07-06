<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Stubs\SearchTestModel;

beforeEach(function () {
    Schema::create('search_test', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->text('notes')->nullable();
    });
});

describe('query generation', function () {
    it('retorna query sem modificação', function (?string $term, array|string $columns) {
        $sql = SearchTestModel::search($term, $columns)->toSql();

        expect($sql)->toBe(SearchTestModel::query()->toSql());
    })->with([
        'quando termo é nulo' => [null, 'name'],
        'quando termo é string vazia' => ['', 'name'],
        'quando colunas são vazias' => ['jose', []],
    ]);
});

describe('search functionality', function () {
    it('encontra registro com acento ao buscar sem acento', function () {
        SearchTestModel::create(['name' => 'José Silva']);

        $results = SearchTestModel::search('jose', 'name')->get();

        expect($results->pluck('name')->all())->toBe(['José Silva']);
    });

    it('encontra registro ao buscar com acento diferente', function () {
        SearchTestModel::create(['name' => 'Ação']);

        $results = SearchTestModel::search('acao', 'name')->get();

        expect($results->pluck('name')->all())->toBe(['Ação']);
    });

    it('não retorna registro que não corresponde ao termo', function () {
        SearchTestModel::create(['name' => 'Carlos']);

        $results = SearchTestModel::search('jose', 'name')->get();

        expect($results)->toBeEmpty();
    });

    it('busca em múltiplas colunas com OR', function () {
        SearchTestModel::create(['name' => 'Carlos', 'notes' => 'reunião']);

        $results = SearchTestModel::search('reuniao', ['name', 'notes'])->get();

        expect($results->pluck('name')->all())->toBe(['Carlos']);
    });

    it('busca em múltiplas colunas retorna registros de colunas diferentes', function () {
        SearchTestModel::create(['name' => 'PHP Avançado', 'notes' => 'Web']);
        SearchTestModel::create(['name' => 'JavaScript', 'notes' => 'Linguagem PHP']);
        SearchTestModel::create(['name' => 'Python', 'notes' => 'Data Science']);

        $results = SearchTestModel::search('php', ['name', 'notes'])->get();

        $names = $results->pluck('name')->all();

        expect($names)
            ->toHaveCount(2)
            ->toContain('PHP Avançado', 'JavaScript');
    });
});

describe('similarity ordering', function () {
    it('resultado mais similar vem primeiro entre dois registros', function () {
        SearchTestModel::create(['name' => 'José Alberto']);
        SearchTestModel::create(['name' => 'José']);

        $results = SearchTestModel::search('jose', 'name')->get();

        expect($results->pluck('name')->all())->toBe(['José', 'José Alberto']);
    });

    it('resultado mais similar vem primeiro entre três registros', function () {
        SearchTestModel::create(['name' => 'Laravel Framework']);
        SearchTestModel::create(['name' => 'Aprendendo Laravel Avançado']);
        SearchTestModel::create(['name' => 'PHP Moderno']);

        $results = SearchTestModel::search('laravel', 'name')->get();

        expect($results->pluck('name')->all())->toBe([
            'Laravel Framework',
            'Aprendendo Laravel Avançado',
        ]);
    });
});
