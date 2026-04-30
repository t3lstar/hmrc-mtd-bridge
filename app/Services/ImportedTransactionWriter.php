<?php

namespace App\Services;

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\ImportBatch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class ImportedTransactionWriter
{
    public function __construct(private TaxYearService $taxYearService) {}

    /**
     * @param  array<string, mixed>  $rawRow
     */
    public function write(
        ImportBatch $batch,
        Business $business,
        CarbonImmutable $transactionDate,
        string $freeagentCategory,
        ?string $description,
        float $amount,
        string $direction,
        array $rawRow,
    ): void {
        $mapping = $this->resolveMapping($freeagentCategory);

        $batch->importedTransactions()->create([
            'business_id' => $business->id,
            'category_mapping_id' => $mapping->id,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'freeagent_category' => $freeagentCategory,
            'description' => blank($description) ? null : trim((string) $description),
            'amount' => round($amount, 2),
            'normalized_amount' => $direction === 'income'
                ? abs(round($amount, 2))
                : -abs(round($amount, 2)),
            'income_or_expense' => $direction,
            'tax_year_start' => $this->taxYearService->taxYearStart($transactionDate),
            'tax_year_quarter' => $this->taxYearService->quarterFor($transactionDate),
            'raw_row' => $rawRow,
        ]);
    }

    private function resolveMapping(string $freeagentCategory): CategoryMapping
    {
        $lookupKey = Str::lower(trim($freeagentCategory));

        $mapping = CategoryMapping::query()->firstOrCreate(
            ['lookup_key' => $lookupKey],
            [
                'freeagent_category' => $freeagentCategory,
                'needs_review' => true,
            ],
        );

        $mapping->forceFill([
            'freeagent_category' => $freeagentCategory,
            'needs_review' => $mapping->hmrc_category_id === null,
        ])->save();

        return $mapping;
    }
}
