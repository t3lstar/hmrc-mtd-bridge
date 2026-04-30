@extends('prototype.layout')

@section('title', 'Category Mappings')
@section('page_badge')
    <flux:badge color="sky" icon="link">Configuration</flux:badge>
@endsection
@section('page_title', 'Category mapping review')
@section('page_intro', 'Map each imported FreeAgent category to an editable HMRC category set before treating the output as submission-ready.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Current blocker count</flux:text>
        <flux:heading size="lg" class="mt-3 text-white">{{ $summary['unmapped_count'] }}</flux:heading>
        <flux:text class="mt-2 text-sm text-zinc-400">Rows in the review bucket block any submission-ready status.</flux:text>
    </flux:card>
@endsection

@section('content')
    <section class="grid gap-6">
        @include('prototype.partials.limitations')

        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Mapping guidance</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Mappings are editable seed data. Review them with accountant input before relying on totals.</flux:text>
                </div>
                <flux:badge color="{{ $summary['unmapped_count'] > 0 ? 'amber' : 'emerald' }}" icon="{{ $summary['unmapped_count'] > 0 ? 'exclamation-triangle' : 'check-circle' }}">
                    {{ $summary['unmapped_count'] > 0 ? $summary['unmapped_count'].' unmapped rows' : 'All mapped' }}
                </flux:badge>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <flux:card class="border border-white/10 bg-black/20 p-4">
                    <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Mappings</flux:text>
                    <flux:heading size="lg" class="mt-2 text-white">{{ $categoryMappings->count() }}</flux:heading>
                </flux:card>
                <flux:card class="border border-white/10 bg-black/20 p-4">
                    <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">HMRC categories</flux:text>
                    <flux:heading size="lg" class="mt-2 text-white">{{ $hmrcCategories->count() }}</flux:heading>
                </flux:card>
                <flux:card class="border border-white/10 bg-black/20 p-4">
                    <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Submission-ready</flux:text>
                    <flux:heading size="lg" class="mt-2 text-white">{{ $summary['submission_ready'] ? 'Yes' : 'No' }}</flux:heading>
                </flux:card>
            </div>
        </flux:card>
    </section>

    <section>
        <flux:card class="space-y-5 border border-white/10 bg-white/6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg" class="text-white">Edit mappings</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-400">Review each category row in place. Status, mapped target, and save action are separated to make the list easier to scan.</flux:text>
                </div>
            </div>

            <flux:table container:class="max-h-[42rem]">
                <flux:table.columns sticky class="bg-zinc-950/90">
                    <flux:table.column>FreeAgent category</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>HMRC category</flux:table.column>
                    <flux:table.column align="end">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($categoryMappings as $mapping)
                        @php($formId = 'mapping-form-'.$mapping->id)
                        <flux:table.row :key="$mapping->id">
                            <flux:table.cell>
                                <form id="{{ $formId }}" method="POST" action="{{ route('category-mappings.update', $mapping) }}" class="hidden">
                                    @csrf
                                    @method('PATCH')
                                </form>

                                <div class="space-y-2">
                                    <flux:heading size="sm" class="text-white">{{ $mapping->freeagent_category }}</flux:heading>
                                    <flux:text class="text-sm text-zinc-400">
                                        {{ $mapping->hmrcCategory?->name ?? 'Unmapped / Needs review' }}
                                    </flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-wrap gap-2">
                                    <flux:badge color="zinc" size="sm">{{ $mapping->imported_transactions_count }} row{{ $mapping->imported_transactions_count === 1 ? '' : 's' }}</flux:badge>
                                    <flux:badge color="{{ $mapping->needs_review ? 'amber' : 'emerald' }}" size="sm">
                                        {{ $mapping->needs_review ? 'Needs review' : 'Mapped' }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="min-w-72">
                                <flux:select form="{{ $formId }}" name="hmrc_category_id" label="HMRC category" placeholder="Unmapped / Needs review">
                                    <flux:select.option value="">Unmapped / Needs review</flux:select.option>
                                    @foreach ($hmrcCategories as $hmrcCategory)
                                        <flux:select.option value="{{ $hmrcCategory->id }}" :selected="$mapping->hmrc_category_id === $hmrcCategory->id">
                                            {{ $hmrcCategory->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <div class="flex justify-end">
                                    <flux:button form="{{ $formId }}" variant="primary" type="submit" size="sm" icon="bookmark-square">Save</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>
@endsection
