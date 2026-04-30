@extends('prototype.layout')

@section('title', 'Business Setup')
@section('page_badge')
    <flux:badge color="sky" icon="building-office-2">Configuration</flux:badge>
@endsection
@section('page_title', 'Business setup')
@section('page_intro', 'Manage the source businesses that feed the combined personal view. Ownership percentages are applied before aggregation.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Configured businesses</flux:text>
        <flux:heading size="lg" class="mt-3 text-white">{{ $businesses->count() }}</flux:heading>
        <flux:text class="mt-2 text-sm text-zinc-400">Keep percentages accurate and credentials separated by source business.</flux:text>
    </flux:card>
@endsection

@section('content')
    <section>
        <flux:card class="space-y-4 border border-white/10 bg-white/6 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="sm" class="text-white">Current business mix</flux:heading>
                <flux:badge color="zinc" icon="calculator">Ownership applied before aggregation</flux:badge>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($businesses as $business)
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-black/20 px-4 py-3">
                        <span class="font-medium text-white">{{ $business->name }}</span>
                        <span class="text-sm text-zinc-400">{{ number_format((float) $business->ownership_percentage, 2) }}%</span>
                    </div>
                @empty
                    <flux:text class="text-sm text-zinc-400">No businesses configured yet.</flux:text>
                @endforelse
            </div>
        </flux:card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="grid gap-6">
            <flux:card class="space-y-5 border border-white/10 bg-white/6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:heading size="lg" class="text-white">Source businesses</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-400">Each business can be updated independently without affecting the raw imported transaction history.</flux:text>
                    </div>
                    <flux:badge color="sky" icon="building-office-2">{{ $businesses->count() }} configured</flux:badge>
                </div>

                <div class="space-y-3">
                    @foreach ($businesses as $business)
                        <form method="POST" action="{{ route('businesses.update', $business) }}" class="grid gap-3 rounded-2xl border border-white/10 bg-black/20 p-4 md:grid-cols-[1.4fr_0.8fr_auto] md:items-end">
                            @csrf
                            @method('PATCH')
                            <flux:input name="name" label="Business name" :value="$business->name" required />
                            <flux:input name="ownership_percentage" label="Ownership %" type="number" step="0.01" min="0" max="100" :value="$business->ownership_percentage" required />
                            <flux:button variant="primary" type="submit" icon="arrow-path">Update</flux:button>
                        </form>
                    @endforeach
                </div>

                <flux:separator class="border-white/10" />

                <form method="POST" action="{{ route('businesses.store') }}" class="grid gap-4 md:grid-cols-[1.4fr_0.8fr_auto] md:items-end">
                    @csrf
                    <flux:input name="name" label="Add business" placeholder="Example: New venture" required />
                    <flux:input name="ownership_percentage" label="Ownership %" type="number" step="0.01" min="0" max="100" placeholder="100" required />
                    <flux:button variant="primary" type="submit" icon="plus">Add business</flux:button>
                </form>
            </flux:card>

            <flux:card class="space-y-5 border border-white/10 bg-white/6">
                <div>
                    <flux:heading size="lg" class="text-white">FreeAgent API credentials</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Store separate OAuth credentials per source business. The API importer uses these credentials, while CSV upload stays available as the fallback path.</flux:text>
                </div>

                <div class="space-y-4">
                    @foreach ($businesses as $business)
                        <form method="POST" action="{{ route('businesses.freeagent-credentials.update', $business) }}" class="space-y-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <flux:heading size="sm" class="text-white">{{ $business->name }}</flux:heading>
                                <flux:badge color="{{ $business->hasFreeAgentCredentials() ? 'emerald' : 'zinc' }}" icon="{{ $business->hasFreeAgentCredentials() ? 'check-circle' : 'key' }}">
                                    {{ $business->hasFreeAgentCredentials() ? 'Credentials configured' : 'Credentials not configured' }}
                                </flux:badge>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:input name="freeagent_client_id" label="Client ID" :value="$business->freeagent_client_id" placeholder="FreeAgent OAuth client ID" />
                                <flux:input name="freeagent_client_secret" label="Client secret" type="password" :value="$business->freeagent_client_secret" placeholder="FreeAgent OAuth client secret" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:input name="freeagent_refresh_token" label="Refresh token" type="password" :value="$business->freeagent_refresh_token" placeholder="Required for API imports" />
                                <flux:input name="freeagent_access_token" label="Access token (optional)" type="password" :value="$business->freeagent_access_token" placeholder="Optional cached access token" />
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <flux:button variant="primary" type="submit" icon="bookmark-square">Save credentials</flux:button>
                                <flux:text class="text-sm text-zinc-400">Leave every field blank and save to clear the stored credentials for this business.</flux:text>
                            </div>
                        </form>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6">
            <flux:card class="space-y-4 border border-white/10 bg-white/6 p-5">
                <flux:badge color="zinc" icon="calculator">Aggregation rule</flux:badge>
                <flux:text class="text-sm text-zinc-300">For this MVP, each business total is adjusted by ownership percentage first, then rolled into one combined personal view.</flux:text>
            </flux:card>
        </div>
    </section>
@endsection
