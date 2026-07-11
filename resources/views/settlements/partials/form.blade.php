<div class="space-y-6">
    <!-- Placeholder para o futuro formulário -->
    <div class="py-8 text-center border-2 border-dashed border-neutral-200 rounded-lg">
        <x-heroicon-o-wrench-screwdriver class="mx-auto h-8 w-8 text-neutral-400" />
        <h3 class="mt-2 text-sm font-semibold text-neutral-900">Formulário em construção</h3>
        <p class="mt-1 text-sm text-neutral-500">O formulário será implementado posteriormente com as integrações financeiras.</p>
    </div>

    <!-- Botões -->
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100">
        <x-button type="button" color="outline" href="{{ route('settlements.contact.show', $contact) }}">
            Cancelar
        </x-button>
        <x-button type="submit" disabled>
            {{ isset($settlement) ? 'Salvar Alterações' : 'Salvar Lançamento' }}
        </x-button>
    </div>
</div>
