<x-layouts.guest>
    <x-auth-header title="Faça login na sua conta" description="Digite seu email e senha abaixo para fazer login" />

    <x-auth-session-status :status="session('status')" />

    <form action="{{ route('login.store') }}" method="POST" class="space-y-6" x-data="{ loading: false }"
        @submit="loading = true">
        @csrf

        {{-- Endereço de email --}}
        <x-form-input label="Endereço de Email" type="email" name="email" :value="old('email')"
            placeholder="email@exemplo.com" required autofocus />

        <div class="relative">
            {{-- Links esqueceu a senha --}}
            <a href="{{ route('password.request') }}"
                class="absolute right-0 top-0 text-sm font-semibold text-neutral-700
              underline underline-offset-4 decoration-neutral-700/30 hover:decoration-neutral-700
             
              transition-colors duration-300">
                Esqueceu sua senha?
            </a>

            {{-- Senha --}}
            <x-form-input label="Senha" type="password" name="password" placeholder="Sua senha" required viewable />
        </div>

        {{-- Checkbox Lembrar de mim --}}
        <x-form-checkbox name="remember" label="Lembrar de mim" />

        {{-- Botão de login --}}
        <x-button type="submit" class="w-full">
            <x-icon name="arrow-right-on-rectangle" class="w-6 h-6" />
            Entrar
        </x-button>

    </form>

</x-layouts.guest>
