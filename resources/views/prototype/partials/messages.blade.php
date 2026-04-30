@if (session('status'))
    <flux:callout color="emerald" icon="check-circle">
        {{ session('status') }}
    </flux:callout>
@endif

@if (session('import_result'))
    @php($importResult = session('import_result'))
    <flux:callout color="{{ $importResult['invalid_rows'] > 0 ? 'amber' : 'emerald' }}" icon="{{ $importResult['invalid_rows'] > 0 ? 'exclamation-triangle' : 'check-circle' }}">
        Imported {{ $importResult['imported_rows'] }} valid row{{ $importResult['imported_rows'] === 1 ? '' : 's' }} for {{ $importResult['business'] }} via {{ $importResult['source'] ?? 'import' }}.
        @if ($importResult['invalid_rows'] > 0)
            {{ $importResult['invalid_rows'] }} invalid row{{ $importResult['invalid_rows'] === 1 ? '' : 's' }} were rejected and reported below.
        @endif
    </flux:callout>

    @if (($importResult['invalid_details'] ?? []) !== [])
        <flux:card class="space-y-4 border border-amber-400/25 bg-black/20">
            <flux:heading size="sm" class="text-white">Recent invalid rows</flux:heading>

            <flux:table container:class="max-h-96">
                <flux:table.columns sticky class="bg-zinc-950/90">
                    <flux:table.column>Row</flux:table.column>
                    <flux:table.column>Reasons</flux:table.column>
                    <flux:table.column>Raw data</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($importResult['invalid_details'] as $invalidRow)
                        <flux:table.row :key="$invalidRow['row_number']">
                            <flux:table.cell variant="strong">#{{ $invalidRow['row_number'] }}</flux:table.cell>
                            <flux:table.cell>{{ implode(' ', $invalidRow['reasons']) }}</flux:table.cell>
                            <flux:table.cell class="max-w-md whitespace-pre-wrap break-words text-xs text-zinc-400">{{ json_encode($invalidRow['raw_row'], JSON_PRETTY_PRINT) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
@endif

@if ($errors->any())
    <flux:callout color="red" icon="x-circle">
        {{ $errors->first() }}
    </flux:callout>
@endif
