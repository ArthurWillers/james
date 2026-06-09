<x-layouts.app>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h2 class="text-2xl font-medium mb-2">
            Perfil
        </h2>
        <p class="mt-1 text-base text-neutral-600">
            Gerencie seu perfil e configurações da conta.
        </p>
    </div>

    <div class="max-w-2xl mx-auto space-y-12 pb-5">
        {{-- Informações do Perfil --}}
        <section>
            <x-section-header title="Informações do Perfil" icon="user" />
            <p class="mt-1 text-sm text-neutral-600">
                Atualize as informações de perfil da sua conta.
            </p>

            <form method="post" action="{{ route('user-profile-information.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('put')

                <div class="space-y-3">
                    <x-form-input name="name" type="text" label="Nome" class="w-full" :value="old('name', auth()->user()->name)"
                        required />

                    <x-form-input name="email" type="email" label="Email" class="w-full" :value="old('email', auth()->user()->email)"
                        required />
                </div>

                <div class="flex items-center gap-4">
                    <x-button type="submit">Salvar</x-button>
                    @if (session('status') === 'profile-information-updated')
                        <p class="text-sm text-green-600 font-medium">Perfil atualizado com sucesso.</p>
                    @endif
                </div>
            </form>
        </section>

        <hr class="border-neutral-200" />

        {{-- Atualizar Senha --}}
        <section>
            <x-section-header title="Atualizar Senha" icon="key" />
            <p class="mt-1 text-sm text-neutral-600">
                Certifique-se de que sua conta esteja usando uma senha longa e aleatória para se manter segura.
            </p>

            <form method="post" action="{{ route('user-password.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('put')

                <div class="space-y-3">
                    <x-form-input name="current_password" type="password" label="Senha Atual" class="w-full"
                        required />

                    <x-form-input name="password" type="password" label="Nova Senha" class="w-full"
                        required />

                    <x-form-input name="password_confirmation" type="password" label="Confirmar Nova Senha" class="w-full"
                        required />
                </div>

                <div class="flex items-center gap-4">
                    <x-button type="submit">Salvar Senha</x-button>
                    @if (session('status') === 'password-updated')
                        <p class="text-sm text-green-600 font-medium">Senha atualizada com sucesso.</p>
                    @endif
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
