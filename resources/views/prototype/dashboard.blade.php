@extends('prototype.layout')

@section('title', 'Dashboard')
@section('page_badge')
    <flux:badge color="sky" icon="chart-bar-square">Dashboard</flux:badge>
@endsection
@section('page_title', 'Configuration and reporting at a glance')
@section('page_intro', 'Review submission readiness, quarter totals, and the next configuration steps for HMRC MTD Bridge.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3">
            <flux:select name="tax_year" label="Tax year" onchange="this.form.submit()">
                @foreach ($taxYearOptions as $taxYear)
                    <flux:select.option value="{{ $taxYear }}" :selected="$selectedTaxYear === $taxYear">{{ sprintf('%d/%02d', $taxYear, ($taxYear + 1) % 100) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:text class="text-sm text-zinc-400">Current reporting window: {{ $taxYearLabel }}</flux:text>
        </form>
    </flux:card>
@endsection

@section('content')
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Submission status</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $summary['submission_ready'] ? 'Ready to review' : 'Needs review' }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-400">
                {{ $summary['submission_ready'] ? 'No unmapped rows are currently blocking a submission-ready view.' : $summary['unmapped_count'].' row'.($summary['unmapped_count'] === 1 ? '' : 's').' remain unmapped.' }}
            </flux:text>
        </flux:card>

        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Configured businesses</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $businesses->count() }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-400">Ownership percentages are applied before personal aggregation.</flux:text>
        </flux:card>

        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Imported rows</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $summary['transaction_count'] }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-400">All raw amounts remain signed internally for auditability.</flux:text>
        </flux:card>

        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Tax year</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $taxYearLabel }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-400">Quarter boundaries follow the UK tax-year cadence from 6 April.</flux:text>
        </flux:card>
    </section>

    <section class="grid gap-6">
        <flux:card class="space-y-4 border border-white/10 bg-white/6 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Current readiness</flux:text>
                    <flux:heading size="sm" class="mt-2 text-white">
                        {{ $summary['submission_ready'] ? 'Submission-ready view available' : 'Mapping review still required' }}
                    </flux:heading>
                </div>
                <flux:badge color="{{ $summary['submission_ready'] ? 'emerald' : 'amber' }}" icon="{{ $summary['submission_ready'] ? 'check-circle' : 'exclamation-triangle' }}">
                    {{ $summary['unmapped_count'] }} blocker{{ $summary['unmapped_count'] === 1 ? '' : 's' }}
                </flux:badge>
            </div>

            <flux:text class="text-sm text-zinc-400">
                {{ $summary['submission_ready']
                    ? 'No imported rows currently fall into the Unmapped / Needs review bucket.'
                    : $summary['unmapped_count'].' row'.($summary['unmapped_count'] === 1 ? '' : 's').' currently fall into the Unmapped / Needs review bucket.' }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-6 border border-white/10 bg-white/6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Quarterly summary</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Income and expenses display as positive values in their own buckets. Net preserves signed arithmetic.</flux:text>
                </div>
                <flux:badge color="{{ $summary['submission_ready'] ? 'emerald' : 'amber' }}" icon="{{ $summary['submission_ready'] ? 'check-circle' : 'exclamation-triangle' }}">
                    {{ $summary['submission_ready'] ? 'No mapping blockers' : 'Mapping review needed' }}
                </flux:badge>
            </div>

            @if (! $summary['has_transactions'])
                <div class="rounded-2xl border border-dashed border-white/10 bg-black/20 p-8 text-center">
                    <flux:heading size="sm" class="text-white">No imported transactions yet</flux:heading>
                    <flux:text class="mt-2 text-zinc-400">Upload a CSV to generate quarter totals, category summaries, and an audit trail.</flux:text>
                </div>
            @else
                <flux:card class="border border-white/10 bg-black/20 p-4">
                    <flux:heading size="sm" class="text-white">Full tax-year summary</flux:heading>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <span class="text-zinc-400">Income</span>
                            <span class="font-semibold text-emerald-300">{{ \Illuminate\Support\Number::currency($summary['year']['income'], 'GBP') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <span class="text-zinc-400">Expenses</span>
                            <span class="font-semibold text-amber-200">{{ \Illuminate\Support\Number::currency($summary['year']['expense'], 'GBP') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <span class="text-zinc-300">Net</span>
                            <span class="font-semibold text-white">{{ \Illuminate\Support\Number::currency($summary['year']['net'], 'GBP') }}</span>
                        </div>
                    </div>
                </flux:card>

                <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-4">
                    @foreach ($summary['combined'] as $quarter)
                        <flux:card class="border border-white/10 bg-black/20 p-4">
                            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">{{ $quarter['label'] }}</flux:text>
                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-zinc-400">Income</span>
                                    <span class="font-medium text-emerald-300">{{ \Illuminate\Support\Number::currency($quarter['income'], 'GBP') }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-zinc-400">Expenses</span>
                                    <span class="font-medium text-amber-200">{{ \Illuminate\Support\Number::currency($quarter['expense'], 'GBP') }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-white/10 pt-2">
                                    <span class="text-zinc-300">Net</span>
                                    <span class="font-semibold text-white">{{ \Illuminate\Support\Number::currency($quarter['net'], 'GBP') }}</span>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>

                <flux:table container:class="max-h-[24rem]">
                    <flux:table.columns sticky class="bg-zinc-950/90">
                        <flux:table.column>Business</flux:table.column>
                        <flux:table.column>Quarter</flux:table.column>
                        <flux:table.column align="end">Income</flux:table.column>
                        <flux:table.column align="end">Expenses</flux:table.column>
                        <flux:table.column align="end">Net</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($summary['businesses'] as $businessSummary)
                            @foreach ($businessSummary['quarters'] as $quarterNumber => $quarter)
                                <flux:table.row :key="$businessSummary['business']->id.'-'.$quarterNumber">
                                    <flux:table.cell variant="strong">{{ $businessSummary['business']->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $quarter['label'] }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency($quarter['income'], 'GBP') }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency($quarter['expense'], 'GBP') }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency($quarter['net'], 'GBP') }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </section>

    <section class="grid gap-6">
        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Recent imports</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Latest CSV activity across all configured businesses.</flux:text>
                </div>
                <flux:button variant="ghost" :href="route('dashboard.imports', ['tax_year' => $selectedTaxYear])" icon="arrow-right">View all</flux:button>
            </div>

            @if ($importBatches->isEmpty())
                <flux:text class="text-sm text-zinc-400">No import batches yet.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Imported</flux:table.column>
                        <flux:table.column>Business</flux:table.column>
                        <flux:table.column align="end">Valid</flux:table.column>
                        <flux:table.column align="end">Invalid</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($importBatches as $batch)
                            <flux:table.row :key="$batch->id">
                                <flux:table.cell>{{ $batch->imported_at?->format('Y-m-d H:i') }}</flux:table.cell>
                                <flux:table.cell variant="strong">{{ $batch->business->name }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $batch->valid_rows }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $batch->invalid_rows }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Audit preview</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">A quick sample of the latest rows in the selected tax year.</flux:text>
                </div>
                <flux:button variant="ghost" :href="route('dashboard.audit', ['tax_year' => $selectedTaxYear])" icon="arrow-right">Open audit</flux:button>
            </div>

            @if ($recentAuditRows->isEmpty())
                <flux:text class="text-sm text-zinc-400">No audit rows yet for {{ $taxYearLabel }}.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Business</flux:table.column>
                        <flux:table.column>HMRC category</flux:table.column>
                        <flux:table.column align="end">Adjusted</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($recentAuditRows as $row)
                            @php($adjustedAmount = round((float) $row->normalized_amount * ((float) $row->business->ownership_percentage / 100), 2))
                            <flux:table.row :key="$row->id">
                                <flux:table.cell>{{ $row->transaction_date->format('Y-m-d') }}</flux:table.cell>
                                <flux:table.cell variant="strong">{{ $row->business->name }}</flux:table.cell>
                                <flux:table.cell>{{ $row->categoryMapping?->hmrcCategory?->name ?? 'Unmapped / Needs review' }}</flux:table.cell>
                                <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency($adjustedAmount, 'GBP') }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </section>
@endsection
