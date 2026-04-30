<?php

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\HmrcCategory;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use App\Services\SummaryService;

test('summary service applies ownership percentage before combined totals', function () {
    $businessA = Business::factory()->create([
        'name' => 'iBPM',
        'ownership_percentage' => 50,
    ]);

    $businessB = Business::factory()->create([
        'name' => 'NOSWAD LLP',
        'ownership_percentage' => 100,
    ]);

    $hmrcCategory = HmrcCategory::factory()->create([
        'code' => 'SE_TURNOVER',
        'name' => 'Self-employment turnover',
        'report_type' => 'self_employment',
        'category_type' => 'income',
    ]);

    $mapping = CategoryMapping::factory()->create([
        'freeagent_category' => 'Sales',
        'lookup_key' => 'sales',
        'hmrc_category_id' => $hmrcCategory->id,
        'needs_review' => false,
    ]);

    $batchA = ImportBatch::factory()->create(['business_id' => $businessA->id]);
    $batchB = ImportBatch::factory()->create(['business_id' => $businessB->id]);

    ImportedTransaction::query()->create([
        'import_batch_id' => $batchA->id,
        'business_id' => $businessA->id,
        'category_mapping_id' => $mapping->id,
        'transaction_date' => '2025-05-01',
        'freeagent_category' => 'Sales',
        'description' => 'Consulting',
        'amount' => 1000,
        'normalized_amount' => 1000,
        'income_or_expense' => 'income',
        'tax_year_start' => 2025,
        'tax_year_quarter' => 1,
        'raw_row' => ['Date' => '2025-05-01'],
    ]);

    ImportedTransaction::query()->create([
        'import_batch_id' => $batchA->id,
        'business_id' => $businessA->id,
        'category_mapping_id' => $mapping->id,
        'transaction_date' => '2025-05-11',
        'freeagent_category' => 'Sales',
        'description' => 'Stationery',
        'amount' => -200,
        'normalized_amount' => -200,
        'income_or_expense' => 'expense',
        'tax_year_start' => 2025,
        'tax_year_quarter' => 1,
        'raw_row' => ['Date' => '2025-05-11'],
    ]);

    ImportedTransaction::query()->create([
        'import_batch_id' => $batchB->id,
        'business_id' => $businessB->id,
        'category_mapping_id' => $mapping->id,
        'transaction_date' => '2025-06-01',
        'freeagent_category' => 'Sales',
        'description' => 'Property income',
        'amount' => 300,
        'normalized_amount' => 300,
        'income_or_expense' => 'income',
        'tax_year_start' => 2025,
        'tax_year_quarter' => 1,
        'raw_row' => ['Date' => '2025-06-01'],
    ]);

    $summary = app(SummaryService::class)->build(2025);

    expect($summary['combined'][1]['income'])->toBe(800.0);
    expect($summary['combined'][1]['expense'])->toBe(100.0);
    expect($summary['combined'][1]['net'])->toBe(700.0);
    expect($summary['year']['income'])->toBe(800.0);
    expect($summary['year']['expense'])->toBe(100.0);
    expect($summary['year']['net'])->toBe(700.0);
    expect($summary['submission_ready'])->toBeTrue();
});

test('summary service marks unmapped rows as not submission ready', function () {
    $business = Business::factory()->create([
        'ownership_percentage' => 100,
    ]);

    $mapping = CategoryMapping::factory()->create([
        'freeagent_category' => 'Unknown Category',
        'lookup_key' => 'unknown category',
        'hmrc_category_id' => null,
        'needs_review' => true,
    ]);

    $batch = ImportBatch::factory()->create(['business_id' => $business->id]);

    ImportedTransaction::query()->create([
        'import_batch_id' => $batch->id,
        'business_id' => $business->id,
        'category_mapping_id' => $mapping->id,
        'transaction_date' => '2025-05-01',
        'freeagent_category' => 'Unknown Category',
        'description' => 'Needs review',
        'amount' => 150,
        'normalized_amount' => 150,
        'income_or_expense' => 'income',
        'tax_year_start' => 2025,
        'tax_year_quarter' => 1,
        'raw_row' => ['Date' => '2025-05-01'],
    ]);

    $summary = app(SummaryService::class)->build(2025);

    expect($summary['submission_ready'])->toBeFalse();
    expect($summary['unmapped_count'])->toBe(1);
    expect($summary['categories'][0]['label'])->toBe('Unmapped / Needs review');
});
