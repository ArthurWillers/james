<?php

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Schema::create('search_test', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->text('notes')->nullable();
    });
});

it('retorna query sem modificação quando termo é nulo', function () {
    $sql = SearchTestModel::search(null, 'name')->toSql();
    expect($sql)->toBe(SearchTestModel::query()->toSql());
});

it('retorna query sem modificação quando termo é string vazia', function () {
    $sql = SearchTestModel::search('', 'name')->toSql();
    expect($sql)->toBe(SearchTestModel::query()->toSql());
});

it('retorna query sem modificação quando colunas são vazias', function () {
    $sql = SearchTestModel::search('jose', [])->toSql();
    expect($sql)->toBe(SearchTestModel::query()->toSql());
});

it('encontra registro com acento ao buscar sem acento', function () {
    SearchTestModel::create(['name' => 'José Silva']);

    $results = SearchTestModel::search('jose', 'name')->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('José Silva');
});

it('encontra registro ao buscar com acento diferente', function () {
    SearchTestModel::create(['name' => 'Ação']);

    $results = SearchTestModel::search('acao', 'name')->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Ação');
});

it('busca em múltiplas colunas com OR', function () {
    SearchTestModel::create(['name' => 'Carlos', 'notes' => 'reunião']);

    $results = SearchTestModel::search('reuniao', ['name', 'notes'])->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Carlos');
});

it('resultado mais similar vem primeiro', function () {
    SearchTestModel::create(['name' => 'José Alberto']);
    SearchTestModel::create(['name' => 'José']);

    $results = SearchTestModel::search('jose', 'name')->get();

    expect($results)->toHaveCount(2);
    expect($results->first()->name)->toBe('José');
    expect($results->last()->name)->toBe('José Alberto');
});

it('não retorna registro que não corresponde ao termo', function () {
    SearchTestModel::create(['name' => 'Carlos']);

    $results = SearchTestModel::search('jose', 'name')->get();

    expect($results)->toHaveCount(0);
});

class SearchTestModel extends Model
{
    use Searchable;

    protected $table = 'search_test';

    protected $fillable = ['name', 'notes'];

    public $timestamps = false;
}
