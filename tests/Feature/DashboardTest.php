<?php

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\HmrcCategory;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;

test('dashboard overview loads the working prototype', function () {
    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Configuration and reporting at a glance')
        ->assertSee('Current readiness')
        ->assertDontSee('Prototype limitations');
});

test('homepage is a simple branded landing page', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('HMRC MTD Bridge')
        ->assertSee('Login')
        ->assertDontSee('Prototype limitations')
        ->assertDontSee('Category mapping review');
});

test('prototype navigation pages load', function (string $routeName, string $expectedText) {
    $response = $this->get(route($routeName));

    $response
        ->assertOk()
        ->assertSee('HMRC MTD Bridge')
        ->assertSee($expectedText);
})->with([
    ['dashboard.businesses', 'Business setup'],
    ['dashboard.imports', 'Import data'],
    ['dashboard.mappings', 'Category mapping review'],
    ['dashboard.categories', 'HMRC category configuration'],
    ['dashboard.audit', 'Audit trail'],
]);

test('business setup prioritizes the current business mix without LLP warning copy', function () {
    $response = $this->get(route('dashboard.businesses'));

    $response
        ->assertOk()
        ->assertSeeInOrder(['Current business mix', 'Source businesses', 'Aggregation rule'])
        ->assertDontSee('LLP treatment still requires accountant validation before relying on real-world outputs.')
        ->assertDontSee('LLP treatment requires accountant validation before real-world reliance.');
});

test('mapping review uses separated table columns for status, notes, and actions', function () {
    $response = $this->get(route('dashboard.mappings'));

    $response
        ->assertOk()
        ->assertSeeInOrder(['FreeAgent category', 'Status', 'HMRC category', 'Action'])
        ->assertDontSee('Review notes');
});

test('category mappings can still be updated from the review grid', function () {
    $hmrcCategory = HmrcCategory::factory()->create();
    $mapping = CategoryMapping::factory()->create([
        'hmrc_category_id' => null,
        'needs_review' => true,
        'notes' => null,
    ]);

    $response = $this->patch(route('category-mappings.update', $mapping), [
        'hmrc_category_id' => $hmrcCategory->id,
        'notes' => 'Reviewed in the improved grid.',
    ]);

    $response
        ->assertRedirect();

    $mapping->refresh();

    expect($mapping->hmrc_category_id)->toBe($hmrcCategory->id)
        ->and($mapping->notes)->toBe('Reviewed in the improved grid.')
        ->and($mapping->needs_review)->toBeFalse();
});

test('overview shows full tax-year summary above quarterly breakdown without standalone quarter boxes', function () {
    $business = Business::factory()->create([
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

    $batch = ImportBatch::factory()->create([
        'business_id' => $business->id,
    ]);

    ImportedTransaction::query()->create([
        'import_batch_id' => $batch->id,
        'business_id' => $business->id,
        'category_mapping_id' => $mapping->id,
        'transaction_date' => '2026-04-20',
        'freeagent_category' => 'Sales',
        'description' => 'Dashboard summary row',
        'amount' => 1000,
        'normalized_amount' => 1000,
        'income_or_expense' => 'income',
        'tax_year_start' => 2026,
        'tax_year_quarter' => 1,
        'raw_row' => ['Date' => '2026-04-20'],
    ]);

    $response = $this->get(route('dashboard', ['tax_year' => 2026]));

    $response
        ->assertOk()
        ->assertSeeInOrder(['Current readiness', 'Quarterly summary', 'Full tax-year summary', 'Q1: 6 Apr - 5 Jul', 'Business', 'Quarter'])
        ->assertDontSee('Quarter 1')
        ->assertDontSee('Prototype limitations');
});
