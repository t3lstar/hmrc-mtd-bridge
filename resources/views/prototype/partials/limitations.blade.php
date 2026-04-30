<flux:card class="space-y-4 border border-amber-400/30 bg-amber-500/10">
    <div class="flex flex-wrap items-center gap-3">
        <flux:badge color="amber" icon="exclamation-triangle">Prototype limitations</flux:badge>
        @if ($summary['submission_ready'])
            <flux:badge color="emerald" icon="check-circle">No unmapped categories</flux:badge>
        @else
            <flux:badge color="red" icon="x-circle">Not submission-ready</flux:badge>
        @endif
    </div>

    <div class="space-y-2 text-sm text-amber-50/90">
        <p>VAT is out of scope. Uploaded figures are treated as reportable net or tax totals from FreeAgent.</p>
        <p>HMRC live submission is not implemented.</p>
        <p>Category mappings are editable starter data and must be reviewed before use.</p>
    </div>

    <flux:separator class="border-amber-200/20" />

    <div class="space-y-2">
        <flux:text class="text-sm text-amber-100">Current readiness</flux:text>
        <flux:text class="text-sm text-amber-50/80">
            {{ $summary['unmapped_count'] }} row{{ $summary['unmapped_count'] === 1 ? '' : 's' }} currently fall into the <span class="font-medium text-white">Unmapped / Needs review</span> bucket.
        </flux:text>
    </div>
</flux:card>
