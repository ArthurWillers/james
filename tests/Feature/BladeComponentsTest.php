<?php

use Illuminate\Support\Facades\Blade;

it('resolves the reorganized anonymous blade components', function () {
    $html = Blade::render(<<<'BLADE'
        <x-breadcrumbs>
            <x-breadcrumbs.item href="/">Início</x-breadcrumbs.item>
            <x-breadcrumbs.item>Atual</x-breadcrumbs.item>
        </x-breadcrumbs>

        <x-filter-bar action="/filters" :filters="[]">
            <x-filter-bar.date name="date" />
            <x-filter-bar.select name="status">
                <option value="active">Ativo</option>
            </x-filter-bar.select>
        </x-filter-bar>

        <x-modal.trigger name="sample-modal">
            <button type="button">Abrir</button>
        </x-modal.trigger>

        <x-modal name="sample-modal" title="Modal de teste" confirm-variant="none">
            <x-slot:content>Conteúdo do modal</x-slot:content>
        </x-modal>

        <x-modal.delete action="/delete" item-name="o registro" />
        <x-modal.restore action="/restore" item-name="o registro" />
    BLADE
    );

    expect($html)
        ->toContain('aria-label="Breadcrumb"')
        ->toContain('name="date"')
        ->toContain('name="status"')
        ->toContain('Modal de teste')
        ->toContain('action="/delete"')
        ->toContain('action="/restore"');
});
