@extends('prototype.layout')

@section('title', 'HMRC Categories')
@section('page_badge')
    <flux:badge color="sky" icon="tag">Configuration</flux:badge>
@endsection
@section('page_title', 'HMRC category configuration')
@section('page_intro', 'Maintain the editable category set used to map FreeAgent categories into self-employment and UK property reporting buckets.')

@section('page_actions')
    <flux:card class="border border-white/10 bg-black/20 p-4">
        <flux:text class="text-xs uppercase tracking-[0.2em] text-zinc-500">Seeded category count</flux:text>
        <flux:heading size="lg" class="mt-3 text-white">{{ $hmrcCategories->count() }}</flux:heading>
        <flux:text class="mt-2 text-sm text-zinc-400">Review starter categories before using them for reporting.</flux:text>
    </flux:card>
@endsection

@section('content')
    <section>
        <flux:card class="space-y-6 border border-white/10 bg-white/6">
            <div>
                <flux:heading size="lg" class="text-white">Add category</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-400">Starter categories should cover self-employment income/expenses and UK property income/expenses for the MVP.</flux:text>
            </div>

            <form method="POST" action="{{ route('hmrc-categories.store') }}" class="grid gap-4 rounded-2xl border border-white/10 bg-black/20 p-4">
                @csrf
                <flux:input name="code" label="Code" placeholder="Example: SE_UTILITIES" required />
                <flux:input name="name" label="Name" placeholder="Category label" required />

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select name="report_type" label="Report type" required>
                        <flux:select.option value="self_employment">self employment</flux:select.option>
                        <flux:select.option value="uk_property">uk property</flux:select.option>
                    </flux:select>
                    <flux:select name="category_type" label="Category type" required>
                        <flux:select.option value="income">income</flux:select.option>
                        <flux:select.option value="expense">expense</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                    <flux:input name="sort_order" label="Sort order" type="number" min="0" value="999" required />
                    <flux:button variant="primary" type="submit" icon="plus">Add category</flux:button>
                </div>
            </form>

            <flux:separator class="border-white/10" />

            <div>
                <flux:heading size="lg" class="text-white">Existing categories</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-400">Update seeded categories in place as the working taxonomy evolves.</flux:text>
            </div>

            <flux:table container:class="max-h-[38rem]">
                <flux:table.columns sticky class="bg-zinc-950/90">
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($hmrcCategories as $hmrcCategory)
                        <flux:table.row :key="$hmrcCategory->id">
                            <flux:table.cell colspan="4" class="p-0">
                                <form method="POST" action="{{ route('hmrc-categories.update', $hmrcCategory) }}" class="grid gap-3 p-3 lg:grid-cols-[0.9fr_1.4fr_1fr_auto] lg:items-end">
                                    @csrf
                                    @method('PATCH')
                                    <flux:input name="code" label="Code" :value="$hmrcCategory->code" required />
                                    <flux:input name="name" label="Name" :value="$hmrcCategory->name" required />

                                    <div class="grid gap-3 md:grid-cols-2">
                                        <flux:select name="report_type" label="Report type" required>
                                            <flux:select.option value="self_employment" :selected="$hmrcCategory->report_type === 'self_employment'">self employment</flux:select.option>
                                            <flux:select.option value="uk_property" :selected="$hmrcCategory->report_type === 'uk_property'">uk property</flux:select.option>
                                        </flux:select>
                                        <flux:select name="category_type" label="Category type" required>
                                            <flux:select.option value="income" :selected="$hmrcCategory->category_type === 'income'">income</flux:select.option>
                                            <flux:select.option value="expense" :selected="$hmrcCategory->category_type === 'expense'">expense</flux:select.option>
                                        </flux:select>
                                    </div>

                                    <div class="flex gap-3">
                                        <input type="hidden" name="sort_order" value="{{ $hmrcCategory->sort_order }}">
                                        <flux:button variant="primary" type="submit" size="sm" icon="bookmark-square">Save</flux:button>
                                    </div>
                                </form>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </section>
@endsection
