<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-neutral-50 text-neutral-900 flex flex-col">
    <main class="flex flex-1 flex-col items-center justify-center p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-6">
            <a href="{{ url('/') }}" class="flex flex-col items-center gap-2 font-medium">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg">
                    <x-app-logo-icon class="size-10" />
                </span>
                <span class="sr-only">{{ config('app.name') }}</span>
            </a>
            
            <div class="w-full">
                {{ $slot }}
            </div>
        </div>
    </main>
    <x-guest-footer />
</body>

</html>
