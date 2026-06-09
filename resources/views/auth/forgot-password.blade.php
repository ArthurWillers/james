<x-layouts.guest>
    <x-auth-header title="Esqueceu a senha" description="Digite seu email para receber um link de redefinição de senha" />

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" x-data="{ loading: false }"
        @submit="loading = true">
        @csrf

        {{-- Endereço de email --}}
        <x-form-input label="Endereço de Email" type="email" name="email" placeholder="email@exemplo.com" required
            autofocus />

        {{-- Botão para enviar link de redefinição de senha --}}
        <x-button type="submit" class="w-full">
            <x-icon name="inbox-arrow-down" class="w-6 h-6" />
            Enviar link de redefinição
        </x-button>

        {{-- Link para login --}}
        <div class="text-center text-sm text-neutral-600">
            <span>Lembrou sua senha?</span>
            <x-link href="{{ route('login') }}">
                Fazer Login
            </x-link>
        </div>
    </form>
</x-layouts.guest>
