<?php

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use App\Models\ImportError;
use Database\Seeders\CategoryMappingSeeder;
use Database\Seeders\HmrcCategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('csv import stores valid rows and reports invalid rows', function () {
    Storage::fake('local');

    $this->seed([
        HmrcCategorySeeder::class,
        CategoryMappingSeeder::class,
    ]);

    $business = Business::factory()->create([
        'name' => 'Test Business',
        'ownership_percentage' => 50,
    ]);

    $csv = <<<'CSV'
Date,Category,Description,Amount,Income or Expense
2025-04-06,Sales,Quarter opener,1200.00,Income
not-a-date,Sales,Invalid row,120.00,Income
2025-07-06,Repairs & Maintenance,Office chair,-80.50,Expense
2025-07-07,Uncategorised Income,Needs review,30.00,Income
CSV;

    $response = $this->post(route('imports.store'), [
        'business_id' => $business->id,
        'file' => UploadedFile::fake()->createWithContent('freeagent.csv', $csv),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('import_result');

    expect(ImportBatch::query()->count())->toBe(1);
    expect(ImportedTransaction::query()->count())->toBe(3);
    expect(ImportError::query()->count())->toBe(1);

    $batch = ImportBatch::query()->first();

    expect($batch->valid_rows)->toBe(3);
    expect($batch->invalid_rows)->toBe(1);

    $salesMapping = CategoryMapping::query()->where('freeagent_category', 'Sales')->first();
    $repairsMapping = CategoryMapping::query()->where('freeagent_category', 'Repairs & Maintenance')->first();
    $unmappedMapping = CategoryMapping::query()->where('freeagent_category', 'Uncategorised Income')->first();

    expect($salesMapping)->not->toBeNull();
    expect($repairsMapping)->not->toBeNull();
    expect($unmappedMapping)->not->toBeNull();
    expect($salesMapping->needs_review)->toBeFalse();
    expect($repairsMapping->needs_review)->toBeFalse();
    expect($unmappedMapping->needs_review)->toBeTrue();
});
