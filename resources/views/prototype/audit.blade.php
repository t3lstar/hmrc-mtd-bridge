@extends('prototype.layout')

@section('title', 'Audit Trail')
@section('page_badge')
    <flux:badge color="sky" icon="document-magnifying-glass">Workspace</flux:badge>
@endsection
@section('page_title', 'Audit trail')
@section('page_intro', 'Trace every imported row with its raw signed amount, mapped HMRC category, ownership-adjusted value, and tax-year quarter.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <form method="GET" action="{{ route('dashboard.audit') }}" class="grid gap-3">
            <flux:select name="tax_year" label="Tax year" onchange="this.form.submit()">
                @foreach ($taxYearOptions as $taxYear)
                    <flux:select.option value="{{ $taxYear }}" :selected="$selectedTaxYear === $taxYear">{{ sprintf('%d/%02d', $taxYear, ($taxYear + 1) % 100) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:text class="text-sm text-zinc-400">Showing rows for {{ $taxYearLabel }}.</flux:text>
        </form>
    </flux:card>
@endsection

@section('content')
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Imported rows</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $summary['transaction_count'] }}</flux:heading>
        </flux:card>
        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Submission blockers</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ $summary['unmapped_count'] }}</flux:heading>
        </flux:card>
        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Income total</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ \Illuminate\Support\Number::currency($summary['year']['income'], 'GBP') }}</flux:heading>
        </flux:card>
        <flux:card class="border border-white/10 bg-white/6 p-5">
            <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Expense total</flux:text>
            <flux:heading size="lg" class="mt-3 text-white">{{ \Illuminate\Support\Number::currency($summary['year']['expense'], 'GBP') }}</flux:heading>
        </flux:card>
    </section>

    <section>
        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Imported transaction log</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Use this view to explain how every total was derived.</flux:text>
                </div>
                <flux:badge color="sky" icon="document-magnifying-glass">{{ $auditRows->total() }} row{{ $auditRows->total() === 1 ? '' : 's' }}</flux:badge>
            </div>

            <flux:table :paginate="$auditRows" pagination:scroll-to container:class="max-h-[38rem]">
                <flux:table.columns sticky class="bg-zinc-950/90">
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>FreeAgent category</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>HMRC category</flux:table.column>
                    <flux:table.column align="end">Raw amount</flux:table.column>
                    <flux:table.column align="end">Adjusted</flux:table.column>
                    <flux:table.column>Quarter</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($auditRows as $row)
                        @php($adjustedAmount = round((float) $row->normalized_amount * ((float) $row->business->ownership_percentage / 100), 2))
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $row->transaction_date->format('Y-m-d') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $row->business->name }}</flux:table.cell>
                            <flux:table.cell>{{ $row->freeagent_category }}</flux:table.cell>
                            <flux:table.cell>{{ $row->description ?: '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $row->categoryMapping?->hmrcCategory?->name ?? 'Unmapped / Needs review' }}</flux:table.cell>
                            <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency((float) $row->amount, 'GBP') }}</flux:table.cell>
                            <flux:table.cell align="end">{{ \Illuminate\Support\Number::currency($adjustedAmount, 'GBP') }}</flux:table.cell>
                            <flux:table.cell>Q{{ $row->tax_year_quarter }} / {{ $row->tax_year_start }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row key="empty-audit">
                            <flux:table.cell colspan="8">No audit rows yet.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>
@endsection
