<x-layouts.app>
    <x-page-header title="Log de Auditoria" />

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Ação</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Entidade</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Autor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Data</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-200">
                            @forelse($activities as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                        #{{ $activity->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">
                                        {{ ucfirst($activity->description) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                        {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                        {{ $activity->causer_id ? ($activity->causer->name ?? 'Usuário #'.$activity->causer_id) : 'Sistema' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                        {{ formatDateTime($activity->created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('audit.show', $activity) }}" class="text-primary-600 hover:text-primary-900">
                                            Ver Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-neutral-500">
                                        Nenhum log de auditoria encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($activities, 'links'))
                    <div class="p-4 border-t border-neutral-200">
                        {{ $activities->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
