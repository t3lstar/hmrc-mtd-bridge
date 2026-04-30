<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ImportBatch;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class FreeAgentImportService
{
    public function __construct(
        private FreeAgentApiClient $freeAgentApiClient,
        private ImportedTransactionWriter $importedTransactionWriter,
        private TaxYearService $taxYearService,
    ) {}

    /**
     * @return array{
     *     batch: ImportBatch,
     *     imported_rows: int,
     *     invalid_rows: int,
     *     invalid_details: array<int, array{row_number:int,reasons:array<int, string>,raw_row:array<string, mixed>}>
     * }
     */
    public function import(Business $business, int $taxYearStart, ?int $quarter = null): array
    {
        if (! $business->hasFreeAgentCredentials()) {
            throw ValidationException::withMessages([
                'business_id' => sprintf('FreeAgent credentials are incomplete for %s.', $business->name),
            ]);
        }

        $periods = collect($this->taxYearService->periodsForTaxYear($taxYearStart))
            ->when($quarter !== null, fn ($collection) => $collection->where('number', $quarter))
            ->values();

        if ($periods->isEmpty()) {
            throw ValidationException::withMessages([
                'quarter' => 'The selected tax-year quarter is invalid.',
            ]);
        }

        $categories = $this->freeAgentApiClient->profitAndLossCategories($business);

        $batch = ImportBatch::query()->create([
            'business_id' => $business->id,
            'original_filename' => $this->batchLabel($taxYearStart, $quarter),
            'stored_filename' => $this->batchReference($taxYearStart, $quarter),
            'imported_at' => now(),
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
        ]);

        $importedRows = 0;

        foreach ($periods as $period) {
            $transactions = $this->freeAgentApiClient->transactionsForPeriod($business, $period['start'], $period['end']);

            foreach ($transactions as $transaction) {
                $category = $categories[$transaction['category'] ?? ''] ?? null;

                if ($category === null) {
                    continue;
                }

                $transactionDate = CarbonImmutable::parse((string) $transaction['dated_on'])->startOfDay();
                $amount = round((float) ($transaction['debit_value'] ?? 0), 2);

                $this->importedTransactionWriter->write(
                    $batch,
                    $business,
                    $transactionDate,
                    (string) ($transaction['category_name'] ?? $category['name']),
                    (string) ($transaction['description'] ?? ''),
                    $amount,
                    $category['direction'],
                    $transaction + [
                        'import_source' => 'freeagent_api',
                        'import_period' => $period['label'],
                    ],
                );

                $importedRows++;
            }
        }

        $batch->forceFill([
            'total_rows' => $importedRows,
            'valid_rows' => $importedRows,
            'invalid_rows' => 0,
        ])->save();

        return [
            'batch' => $batch->fresh('business'),
            'imported_rows' => $importedRows,
            'invalid_rows' => 0,
            'invalid_details' => [],
        ];
    }

    private function batchLabel(int $taxYearStart, ?int $quarter): string
    {
        $taxYearLabel = $this->taxYearService->label($taxYearStart);

        if ($quarter === null) {
            return sprintf('FreeAgent API %s all quarters', $taxYearLabel);
        }

        return sprintf('FreeAgent API %s Q%d', $taxYearLabel, $quarter);
    }

    private function batchReference(int $taxYearStart, ?int $quarter): string
    {
        return sprintf(
            'freeagent-api://tax-years/%d/%s',
            $taxYearStart,
            $quarter === null ? 'all' : 'q'.$quarter,
        );
    }
}
