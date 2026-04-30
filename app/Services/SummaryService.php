<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ImportedTransaction;
use Illuminate\Support\Collection;

class SummaryService
{
    public function __construct(private TaxYearService $taxYearService) {}

    /**
     * @return Collection<int, int>
     */
    public function availableTaxYears(): Collection
    {
        $years = ImportedTransaction::query()
            ->select('tax_year_start')
            ->distinct()
            ->orderByDesc('tax_year_start')
            ->pluck('tax_year_start')
            ->map(fn ($value): int => (int) $value);

        if ($years->isNotEmpty()) {
            return $years;
        }

        return collect([$this->taxYearService->currentTaxYearStart()]);
    }

    /**
     * @return array{
     *     has_transactions: bool,
     *     submission_ready: bool,
     *     unmapped_count: int,
     *     combined: array<int, array{label:string,income:float,expense:float,net:float}>,
     *     businesses: array<int, array{business: Business, quarters: array<int, array{label:string,income:float,expense:float,net:float}>, year: array{income:float,expense:float,net:float}}>,
     *     categories: array<int, array{label:string,report_type:string,category_type:string,income:float,expense:float,net:float,needs_review:bool}>,
     *     year: array{income:float,expense:float,net:float},
     *     transaction_count: int
     * }
     */
    public function build(int $taxYearStart): array
    {
        $transactions = ImportedTransaction::query()
            ->with(['business', 'categoryMapping.hmrcCategory'])
            ->where('tax_year_start', $taxYearStart)
            ->orderBy('transaction_date')
            ->get();

        $periods = collect($this->taxYearService->periodsForTaxYear($taxYearStart))
            ->keyBy('number');

        $combined = $periods->mapWithKeys(fn (array $period, int $number): array => [
            $number => $this->makeQuarterBucket($period['label']),
        ])->all();

        $businessBuckets = Business::query()
            ->orderBy('name')
            ->get()
            ->map(function (Business $business) use ($periods): array {
                return [
                    'business' => $business,
                    'quarters' => $periods->mapWithKeys(fn (array $period, int $number): array => [
                        $number => $this->makeQuarterBucket($period['label']),
                    ])->all(),
                    'year' => $this->makeTotalsBucket(),
                ];
            })
            ->keyBy(fn (array $entry): int => $entry['business']->id)
            ->all();

        $categoryBuckets = [];
        $unmappedCount = 0;
        $yearTotals = $this->makeTotalsBucket();

        foreach ($transactions as $transaction) {
            $adjustedAmount = $this->adjustedAmount($transaction);
            $quarter = $transaction->tax_year_quarter;
            $businessId = $transaction->business_id;

            $this->addAmountToBucket($combined[$quarter], $adjustedAmount);
            $this->addAmountToBucket($businessBuckets[$businessId]['quarters'][$quarter], $adjustedAmount);
            $this->addAmountToBucket($businessBuckets[$businessId]['year'], $adjustedAmount);
            $this->addAmountToBucket($yearTotals, $adjustedAmount);

            $hmrcCategory = $transaction->categoryMapping?->hmrcCategory;
            $categoryKey = $hmrcCategory?->id ?? 0;

            if (! array_key_exists($categoryKey, $categoryBuckets)) {
                $categoryBuckets[$categoryKey] = [
                    'label' => $hmrcCategory?->name ?? 'Unmapped / Needs review',
                    'report_type' => $hmrcCategory?->report_type ?? 'review',
                    'category_type' => $hmrcCategory?->category_type ?? $transaction->income_or_expense,
                    'income' => 0.0,
                    'expense' => 0.0,
                    'net' => 0.0,
                    'needs_review' => $hmrcCategory === null,
                ];
            }

            $this->addAmountToBucket($categoryBuckets[$categoryKey], $adjustedAmount);

            if ($hmrcCategory === null) {
                $unmappedCount++;
            }
        }

        return [
            'has_transactions' => $transactions->isNotEmpty(),
            'submission_ready' => $unmappedCount === 0,
            'unmapped_count' => $unmappedCount,
            'combined' => $combined,
            'businesses' => array_values($businessBuckets),
            'categories' => collect($categoryBuckets)
                ->sortBy([
                    ['needs_review', 'desc'],
                    ['report_type', 'asc'],
                    ['label', 'asc'],
                ])
                ->values()
                ->all(),
            'year' => $yearTotals,
            'transaction_count' => $transactions->count(),
        ];
    }

    public function adjustedAmount(ImportedTransaction $transaction): float
    {
        $ownershipShare = (float) $transaction->business->ownership_percentage / 100;

        return round((float) $transaction->normalized_amount * $ownershipShare, 2);
    }

    /**
     * @return array{label:string,income:float,expense:float,net:float}
     */
    private function makeQuarterBucket(string $label): array
    {
        return ['label' => $label] + $this->makeTotalsBucket();
    }

    /**
     * @return array{income:float,expense:float,net:float}
     */
    private function makeTotalsBucket(): array
    {
        return [
            'income' => 0.0,
            'expense' => 0.0,
            'net' => 0.0,
        ];
    }

    /**
     * @param  array{income:float,expense:float,net:float}|array{label:string,income:float,expense:float,net:float}  $bucket
     */
    private function addAmountToBucket(array &$bucket, float $adjustedAmount): void
    {
        if ($adjustedAmount >= 0) {
            $bucket['income'] += $adjustedAmount;
        } else {
            $bucket['expense'] += abs($adjustedAmount);
        }

        $bucket['net'] += $adjustedAmount;
    }
}
