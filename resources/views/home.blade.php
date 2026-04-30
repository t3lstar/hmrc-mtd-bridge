<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @php($title = 'HMRC MTD Bridge')
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-50">
        <div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(161,161,170,0.18),_transparent_32%),linear-gradient(180deg,_#18181b_0%,_#09090b_100%)] px-6">
            <flux:card class="w-full max-w-xl space-y-8 border border-white/10 bg-white/6 p-10 text-center shadow-2xl shadow-black/30">
                <div class="mx-auto flex w-fit justify-center">
                    <x-app-logo href="{{ route('home') }}" />
                </div>

                <div class="space-y-4">
                    <flux:heading size="xl" level="1" class="text-white">HMRC MTD Bridge</flux:heading>
                    <flux:text class="text-base text-zinc-300">A prototype bridge between FreeAgent exports and quarterly MTD reporting workflows.</flux:text>
                </div>

                <div class="flex justify-center">
                    <flux:button variant="primary" :href="route('login')" wire:navigate icon="arrow-right-start-on-rectangle">
                        Login
                    </flux:button>
                </div>
            </flux:card>
        </div>

        @fluxScripts
    </body>
</html>
