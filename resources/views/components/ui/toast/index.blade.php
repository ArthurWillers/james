@php
    $hasInitialToast = session()->has('toast') || session()->has('success');

    if (session()->has('toast')) {
        $toast = session('toast');
        $initialType = $toast['type'] ?? 'info';
        $initialMessage = $toast['message'] ?? '';
    } else {
        $initialType = 'success';
        $initialMessage = session('success', '');
    }
@endphp

<div class="fixed top-0 inset-x-0 flex items-start justify-center lg:justify-end p-4 z-[60] pointer-events-none">

        <div x-data="{
                show: {{ $hasInitialToast ? 'true' : 'false' }},
                progress: 100,
                message: {{ Js::from($initialMessage) }},
                type: {{ Js::from($initialType) }},
                timer: null,
                interval: null,
                init() {
                    window.addEventListener('toast', (event) => this.showToast(event.detail));

                    if (this.show) {
                        this.startTimer();
                    }
                },
                showToast(detail) {
                    this.message = detail?.message ?? '';
                    this.type = detail?.type ?? 'info';
                    this.show = true;
                    this.startTimer();
                },
                startTimer() {
                    clearTimeout(this.timer);
                    clearInterval(this.interval);
                    this.progress = 100;
                    this.interval = setInterval(() => {
                        this.progress -= 2;
                        if (this.progress <= 0) {
                            clearInterval(this.interval);
                        }
                    }, 50);
                    this.timer = setTimeout(() => this.show = false, 2500);
                },
                close() {
                    clearTimeout(this.timer);
                    clearInterval(this.interval);
                    this.show = false;
                }
            }" x-show="show" x-cloak
            x-transition:enter="transition motion-ease-smooth-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition motion-ease-smooth-out motion-duration-medium transform"
            x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0"
            class="w-full max-w-sm rounded-lg shadow-lg border border-neutral-300 bg-white overflow-hidden pointer-events-auto">
            <div class="flex items-center gap-4 p-4">
                {{-- Ícone --}}
                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full shrink-0" x-show="type === 'success'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                </div>
                <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-full shrink-0" x-show="type === 'error'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                </div>
                <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 rounded-full shrink-0" x-show="type === 'warning'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-yellow-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                </div>
                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full shrink-0" x-show="type !== 'success' && type !== 'error' && type !== 'warning'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                </div>
                {{-- Mensagem e Botão de Fechar --}}
                <div class="flex-1" x-text="message"></div>
                <button class="cursor-pointer shrink-0" @click="close()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d=" M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Barra de Progresso --}}
            <div class="w-full bg-neutral-200 h-1">
                <div class="h-1" :class="{
                    'bg-green-600': type === 'success',
                    'bg-red-600': type === 'error',
                    'bg-yellow-600': type === 'warning',
                    'bg-blue-600': type !== 'success' && type !== 'error' && type !== 'warning'
                }" :style="`width: ${progress}%`"></div>
            </div>
        </div>
</div>
