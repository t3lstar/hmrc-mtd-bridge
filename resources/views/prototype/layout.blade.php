<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @php($title = trim($__env->yieldContent('title', 'Dashboard')))
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-50">
        <flux:header class="border-b border-white/10 bg-zinc-950/90 backdrop-blur lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <div class="min-w-0">
                <flux:heading size="sm" class="truncate text-white">@yield('page_title', 'Dashboard')</flux:heading>
            </div>
            <flux:spacer />
            <flux:button variant="ghost" :href="route('home')" icon="home">Home</flux:button>
        </flux:header>

        <flux:sidebar sticky collapsible="mobile" class="border-e border-white/10 bg-zinc-950/90 backdrop-blur">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Workspace" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('dashboard', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard')">
                        Overview
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-up-tray" :href="route('dashboard.imports', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard.imports')">
                        Imports
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-magnifying-glass" :href="route('dashboard.audit', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard.audit')">
                        Audit trail
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Configuration" class="grid">
                    <flux:sidebar.item icon="building-office-2" :href="route('dashboard.businesses', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard.businesses')">
                        Businesses
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="link" :href="route('dashboard.mappings', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard.mappings')" :badge="$summary['unmapped_count'] > 0 ? (string) $summary['unmapped_count'] : null">
                        Mappings
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('dashboard.categories', ['tax_year' => $selectedTaxYear])" :current="request()->routeIs('dashboard.categories')">
                        HMRC categories
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('home')">Homepage</flux:sidebar.item>
                <flux:sidebar.item icon="arrow-right-start-on-rectangle" :href="route('login')">Login</flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        <flux:main class="bg-[radial-gradient(circle_at_top,_rgba(161,161,170,0.18),_transparent_30%),linear-gradient(180deg,_#18181b_0%,_#09090b_100%)]">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-6 py-8 lg:px-8">
                <section class="flex flex-wrap items-start justify-between gap-6">
                    <div class="max-w-3xl space-y-4">
                        @hasSection('page_badge')
                            @yield('page_badge')
                        @endif

                        <div class="space-y-3">
                            <flux:heading size="xl" level="1" class="text-white">@yield('page_title', 'Dashboard')</flux:heading>
                            @hasSection('page_intro')
                                <flux:text class="max-w-2xl text-base text-zinc-300">@yield('page_intro')</flux:text>
                            @endif
                        </div>
                    </div>

                    @hasSection('page_actions')
                        <div class="w-full max-w-sm">
                            @yield('page_actions')
                        </div>
                    @endif
                </section>

                @include('prototype.partials.messages')

                @yield('content')
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>
