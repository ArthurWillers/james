@if (session()->has('toast'))
    @php
        $toast = session('toast');
        $type = $toast['type'] ?? 'info';
        $message = $toast['message'] ?? '';
        $progressColor = match ($type) {
            'success' => 'bg-green-600',
            'error' => 'bg-red-600',
            'warning' => 'bg-yellow-600',
            default => 'bg-blue-600',
        };
    @endphp

    <div class="fixed top-0 inset-x-0 flex items-start justify-center lg:justify-end p-4 z-50 pointer-events-none">

        <div x-data="{ show: false, progress: 100 }" x-init="$nextTick(() => {
            show = true;
            const interval = setInterval(() => {
                progress -= 1;
                if (progress <= 0) {
                    clearInterval(interval);
                    show = false;
                }
            }, 50);
        });"x-show="show" x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0"
            class="w-full max-w-sm rounded-lg shadow-lg border border-neutral-300 bg-white overflow-hidden pointer-events-auto">
            <div class="flex items-center gap-4 p-4">
                {{-- Ícone --}}
                @if ($type === 'success')
                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                @elseif ($type === 'error')
                    <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-full flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </div>
                @elseif ($type === 'warning')
                    <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 rounded-full flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-yellow-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                    </div>
                @endif
                {{-- Mensagem e Botão de Fechar --}}
                <div class="flex-1">{{ $message }}</div>
                <button class="cursor-pointer flex-shrink-0" @click="show = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d=" M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Barra de Progresso --}}
            <div class="w-full bg-neutral-200 h-1">
                <div class="{{ $progressColor }} h-1 " :style="`width: ${progress}%`"></div>
            </div>
        </div>
    </div>
@endif
