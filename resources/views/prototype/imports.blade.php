@extends('prototype.layout')

@section('title', 'Imports')
@section('page_badge')
    <flux:badge color="sky" icon="arrow-up-tray">Workspace</flux:badge>
@endsection
@section('page_title', 'Import data')
@section('page_intro', 'Import FreeAgent data from the API or by CSV fallback, then review normalized results in the shared audit trail.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Recent import batches</flux:text>
        <flux:heading size="lg" class="mt-3 text-white">{{ $importBatches->count() }}</flux:heading>
        <flux:text class="mt-2 text-sm text-zinc-400">API and CSV imports both land in the same normalized store.</flux:text>
    </flux:card>
@endsection

@section('content')
    <section class="grid gap-6 xl:grid-cols-2">
        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <flux:heading size="lg" class="text-white">Import from FreeAgent API</flux:heading>
                    <flux:badge color="emerald" icon="cloud-arrow-down">Primary</flux:badge>
                </div>
                <flux:text class="mt-1 text-sm text-zinc-400">Fetch quarter-bounded accounting transactions for a UK tax year, then normalize the P&amp;L rows into the same transaction table used by CSV imports.</flux:text>
            </div>

            <form method="POST" action="{{ route('imports.freeagent.store') }}" class="grid gap-4">
                @csrf
                <flux:select name="business_id" label="Source business" placeholder="Choose a business..." required>
                    @foreach ($businesses as $business)
                        <flux:select.option value="{{ $business->id }}">{{ $business->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select name="tax_year_start" label="UK tax year" required>
                        @foreach ($importTaxYearOptions as $taxYear)
                            <flux:select.option value="{{ $taxYear }}" :selected="$selectedTaxYear === $taxYear">{{ sprintf('%d/%02d', $taxYear, ($taxYear + 1) % 100) }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select name="quarter" label="Quarter">
                        <flux:select.option value="">All quarters</flux:select.option>
                        <flux:select.option value="1">Q1: 6 Apr - 5 Jul</flux:select.option>
                        <flux:select.option value="2">Q2: 6 Jul - 5 Oct</flux:select.option>
                        <flux:select.option value="3">Q3: 6 Oct - 5 Jan</flux:select.option>
                        <flux:select.option value="4">Q4: 6 Jan - 5 Apr</flux:select.option>
                    </flux:select>
                </div>

                <div class="rounded-2xl border border-dashed border-white/10 bg-black/20 p-4 text-sm text-zinc-400">
                    Configure FreeAgent OAuth client credentials and a refresh token on the <span class="font-medium text-white">Business setup</span> page before using API imports.
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <flux:button variant="primary" type="submit" icon="cloud-arrow-down">Import from API</flux:button>
                    <flux:button variant="ghost" :href="route('dashboard.businesses', ['tax_year' => $selectedTaxYear])" icon="key">Manage business credentials</flux:button>
                </div>
            </form>
        </flux:card>

        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <flux:heading size="lg" class="text-white">Upload FreeAgent CSV</flux:heading>
                    <flux:badge color="zinc" icon="document-text">Fallback</flux:badge>
                </div>
                <flux:text class="mt-1 text-sm text-zinc-400">The CSV importer remains available for one expected FreeAgent export shape, with tolerant header matching for minor variations such as Date/date and Category/category.</flux:text>
            </div>

            <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="grid gap-4">
                @csrf
                <flux:select name="business_id" label="Source business" placeholder="Choose a business..." required>
                    @foreach ($businesses as $business)
                        <flux:select.option value="{{ $business->id }}">{{ $business->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input name="file" label="CSV file" type="file" accept=".csv,text/csv" required />

                <div class="rounded-2xl border border-dashed border-white/10 bg-black/20 p-4 text-sm text-zinc-400">
                    Expected columns: <span class="font-medium text-white">Date</span>, <span class="font-medium text-white">Category</span>, <span class="font-medium text-white">Description</span>, <span class="font-medium text-white">Amount</span>, <span class="font-medium text-white">Income or Expense</span>.
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <flux:button variant="primary" type="submit" icon="arrow-up-tray">Import CSV</flux:button>
                    <flux:badge color="zinc" icon="document-text">Rejected rows are reported with row number and reason</flux:badge>
                </div>
            </form>
        </flux:card>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <flux:card class="space-y-4 border border-white/10 bg-white/6 p-5">
            <flux:badge color="zinc" icon="clipboard-document-check">Import behavior</flux:badge>
            <div class="space-y-2 text-sm text-zinc-300">
                <p>VAT is out of scope. Figures are treated as reportable net or tax totals from FreeAgent.</p>
                <p>API and CSV imports both normalize into the same shared transaction store.</p>
                <p>Valid rows import even when CSV rows fail validation.</p>
                <p>Nothing is silently discarded.</p>
            </div>
        </flux:card>

        <flux:card class="space-y-4 border border-white/10 bg-white/6 p-5">
            <flux:badge color="zinc" icon="bookmark-square">Before importing</flux:badge>
            <div class="space-y-2 text-sm text-zinc-300">
                <p>Confirm the business ownership percentage is current.</p>
                <p>Configure separate FreeAgent credentials per source business if using the API path.</p>
                <p>Check the CSV shape if using the fallback upload path.</p>
                <p>Review category mappings after the first import.</p>
            </div>
        </flux:card>
    </section>

    <section>
        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Import history</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Recent batches across all businesses.</flux:text>
                </div>
            </div>

            @if ($importBatches->isEmpty())
                <flux:text class="text-sm text-zinc-400">No import batches yet.</flux:text>
            @else
                <flux:table container:class="max-h-[34rem]">
                    <flux:table.columns sticky class="bg-zinc-950/90">
                        <flux:table.column>Imported</flux:table.column>
                        <flux:table.column>Business</flux:table.column>
                        <flux:table.column align="end">Total</flux:table.column>
                        <flux:table.column align="end">Valid</flux:table.column>
                        <flux:table.column align="end">Invalid</flux:table.column>
                        <flux:table.column>Filename</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($importBatches as $batch)
                            <flux:table.row :key="$batch->id">
                                <flux:table.cell>{{ $batch->imported_at?->format('Y-m-d H:i') }}</flux:table.cell>
                                <flux:table.cell variant="strong">{{ $batch->business->name }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $batch->total_rows }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $batch->valid_rows }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $batch->invalid_rows }}</flux:table.cell>
                                <flux:table.cell>{{ $batch->original_filename }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </section>
@endsection
