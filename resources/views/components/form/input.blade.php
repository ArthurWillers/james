@props([
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'viewable' => false,
    'numeric' => false,
    'currency' => false,
    'bag' => 'default',
    'hasError' => false, // Override para forçar estado de erro
])

@php
    $baseClasses = 'w-full border appearance-none text-sm rounded-xl block py-2.5 px-4 bg-white disabled:shadow-none shadow-xs focus:shadow-lg text-neutral-700 disabled:text-neutral-400 placeholder-neutral-400 disabled:placeholder-neutral-400/70 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors duration-300';
    $isError = $hasError || ($name && $errors->getBag($bag)->has($name));
    $errorClasses = $isError
        ? 'border-red-500 focus:border-red-500 focus:ring-red-400/30'
        : 'border-neutral-200';
    $classes = $baseClasses . ' ' . $errorClasses;
@endphp

<div class="relative w-full" 
    @if ($viewable) x-data="{ show: false }" @endif
    @if ($currency) x-data="{
        rawValue: '{{ $value }}',
        formatCurrency(val) {
            let str = String(val || '').replace(/\D/g, '');
            if (str === '') str = '0';
            let num = parseInt(str, 10);
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(num / 100);
        },
        updateValue(e) {
            let val = e.target.value;
            let str = String(val).replace(/\D/g, '');
            if (str === '') str = '0';
            let num = parseInt(str, 10);
            let floatVal = (num / 100).toFixed(2);
            if (this.rawValue !== floatVal) {
                this.rawValue = floatVal;
            }
            e.target.value = this.formatCurrency(str);
        },
        init() {
            this.$watch('rawValue', (val) => {
                let parsed = parseFloat(val || 0);
                if (isNaN(parsed)) parsed = 0;
                let str = parsed.toFixed(2).replace(/\D/g, '');
                this.$refs.display.value = this.formatCurrency(str);
            });
            
            let parsed = parseFloat(this.rawValue || 0);
            if (isNaN(parsed)) parsed = 0;
            let str = parsed.toFixed(2).replace(/\D/g, ''); 
            this.$refs.display.value = this.formatCurrency(str);
            
            let floatVal = parsed.toFixed(2);
            if (this.rawValue !== floatVal) {
                this.rawValue = floatVal;
            }
        }
    }" 
    x-modelable="rawValue" 
    {{ $attributes->only('x-model') }} 
    @endif>

    @if ($currency)
        <input type="hidden" @if ($name) id="{{ $name }}" name="{{ $name }}" @endif x-model="rawValue">
        <input type="text" inputmode="numeric" x-ref="display" @input="updateValue($event)" placeholder="{{ $placeholder }}" {{ $attributes->whereDoesntStartWith('x-model')->merge(['class' => $classes]) }} />
    @else
        <input
            @if ($viewable) :type="show ? 'text' : 'password'"
            @else type="{{ $numeric ? 'text' : $type }}" @endif
            @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
            placeholder="{{ $placeholder }}"
            value="{{ $value }}" {{ $attributes->merge(['class' => $classes]) }}
            @if ($numeric) x-data
                @input="$event.target.value = $event.target.value.replace(/[^0-9.,]/g, '')"
                inputmode="numeric" @endif />
    @endif

    @if ($viewable)
        <div class="absolute top-0 bottom-0 flex items-center gap-x-1.5 pe-3 inset-e-0 text-xs text-neutral-400">
            <button type="button" x-on:click="show = !show" tabindex="-1"
                class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 inline-flex -ms-1.5 -me-1.5 bg-transparent hover:bg-neutral-800/5 text-neutral-500 hover:text-neutral-800 transition-colors duration-300 cursor-pointer">
                {{-- Ícone de "escondido" (olho cortado) --}}
                <x-heroicon-o-eye-slash class="size-4" x-show="!show" />

                {{-- Ícone de "visível" (olho aberto) --}}
                <x-heroicon-o-eye class="size-4" style="display: none;" x-show="show" />
            </button>
        </div>
    @endif
</div>
