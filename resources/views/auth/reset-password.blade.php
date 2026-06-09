<x-layouts.guest>
    <x-auth-header title="Esqueceu a senha" description="Digite seu email para receber um link de redefinição de senha" />

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" x-data="{ loading: false, showRules: false }"
        @submit="loading = true">
        @csrf

        {{-- Token de Redefinição de Senha --}}
        <input type="hidden" name="token" value="{{ request('token') }}">

        {{-- Endereço de email --}}
        <x-form-input label="Endereço de Email" type="email" name="email" :value="request('email') ?? old('email')"
            placeholder="email@exemplo.com" required readonly />

        {{-- Senha --}}
        <x-form-input label="Nova Senha" type="password" name="password" placeholder="Sua nova senha" required autofocus
            viewable />

        {{-- Confirmar Senha --}}
        <x-form-input label="Confirmar Nova Senha" type="password" name="password_confirmation"
            placeholder="Confirme sua nova senha" required viewable />

        {{-- Regras de senha --}}
        <x-password-rules />

        {{-- Botão para redefinir senha --}}
        <x-button type="submit" class="w-full">
            <x-icon name="key" class="w-6 h-6" />
            Redefinir Senha
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
