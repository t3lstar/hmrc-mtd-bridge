<?php

namespace App\Services;

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\ImportBatch;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CsvImportService
{
    public function __construct(private TaxYearService $taxYearService) {}

    /**
     * @return array{
     *     batch: ImportBatch,
     *     imported_rows: int,
     *     invalid_rows: int,
     *     invalid_details: array<int, array{row_number:int,reasons:array<int, string>,raw_row:array<string, mixed>}>
     * }
     */
    public function import(Business $business, UploadedFile $file): array
    {
        $storedFilename = $file->store('imports', 'local');
        $csvPath = Storage::disk('local')->path($storedFilename);

        $handle = fopen($csvPath, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded CSV could not be opened.',
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => 'The uploaded CSV is empty.',
            ]);
        }

        $headerMap = $this->resolveHeaderMap($header);

        $batch = ImportBatch::query()->create([
            'business_id' => $business->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'imported_at' => now(),
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
        ]);

        $rowNumber = 1;
        $totalRows = 0;
        $validRows = 0;
        $invalidRows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $totalRows++;
            $rawRow = $this->combineRow($headerMap, $row);
            $reasons = $this->validateRow($rawRow);

            if ($reasons !== []) {
                $invalidRows[] = [
                    'row_number' => $rowNumber,
                    'raw_row' => $rawRow,
                    'reasons' => $reasons,
                ];

                continue;
            }

            $transactionDate = $this->parseDate((string) $rawRow['date']);
            $amount = $this->parseAmount((string) $rawRow['amount']);
            $direction = $this->normalizeDirection((string) $rawRow['income_or_expense']);
            $normalizedAmount = $direction === 'income'
                ? abs($amount)
                : -abs($amount);
            $freeagentCategory = trim((string) $rawRow['freeagent_category']);
            $lookupKey = Str::lower($freeagentCategory);

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

            $batch->importedTransactions()->create([
                'business_id' => $business->id,
                'category_mapping_id' => $mapping->id,
                'transaction_date' => $transactionDate->format('Y-m-d'),
                'freeagent_category' => $freeagentCategory,
                'description' => trim((string) ($rawRow['description'] ?? '')) ?: null,
                'amount' => $amount,
                'normalized_amount' => $normalizedAmount,
                'income_or_expense' => $direction,
                'tax_year_start' => $this->taxYearService->taxYearStart($transactionDate),
                'tax_year_quarter' => $this->taxYearService->quarterFor($transactionDate),
                'raw_row' => $rawRow,
            ]);

            $validRows++;
        }

        fclose($handle);

        foreach ($invalidRows as $invalidRow) {
            $batch->importErrors()->create($invalidRow);
        }

        $batch->forceFill([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'invalid_rows' => count($invalidRows),
        ])->save();

        return [
            'batch' => $batch->fresh(['business', 'importErrors']),
            'imported_rows' => $validRows,
            'invalid_rows' => count($invalidRows),
            'invalid_details' => $invalidRows,
        ];
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array<string, int|null>
     */
    private function resolveHeaderMap(array $header): array
    {
        $aliases = [
            'date' => ['date'],
            'freeagent_category' => ['category', 'freeagentcategory'],
            'description' => ['description', 'details', 'memo', 'narrative'],
            'amount' => ['amount', 'value', 'netamount', 'total'],
            'income_or_expense' => ['incomeorexpense', 'type', 'direction', 'incomeexpense'],
        ];

        $normalizedHeader = collect($header)
            ->map(fn (?string $value): string => $this->normalizeHeader((string) $value))
            ->values();

        $headerMap = [];

        foreach ($aliases as $field => $possibleValues) {
            $index = $normalizedHeader->search(
                fn (string $value): bool => in_array($value, $possibleValues, true),
            );

            if ($index === false && $field !== 'description') {
                throw ValidationException::withMessages([
                    'file' => sprintf('The CSV is missing the required "%s" column.', $field),
                ]);
            }

            $headerMap[$field] = $index === false ? null : $index;
        }

        return $headerMap;
    }

    /**
     * @param  array<string, int|null>  $headerMap
     * @param  array<int, string|null>  $row
     * @return array<string, mixed>
     */
    private function combineRow(array $headerMap, array $row): array
    {
        return collect($headerMap)
            ->mapWithKeys(fn (?int $index, string $field): array => [
                $field => $index === null ? null : ($row[$index] ?? null),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, string>
     */
    private function validateRow(array $row): array
    {
        $reasons = [];

        if (blank(trim((string) ($row['date'] ?? '')))) {
            $reasons[] = 'Missing date.';
        } elseif ($this->parseDateOrNull((string) $row['date']) === null) {
            $reasons[] = 'The date could not be parsed.';
        }

        if (blank(trim((string) ($row['freeagent_category'] ?? '')))) {
            $reasons[] = 'Missing FreeAgent category.';
        }

        if (blank(trim((string) ($row['amount'] ?? '')))) {
            $reasons[] = 'Missing amount.';
        } elseif ($this->parseAmountOrNull((string) $row['amount']) === null) {
            $reasons[] = 'The amount is not numeric.';
        }

        if (blank(trim((string) ($row['income_or_expense'] ?? '')))) {
            $reasons[] = 'Missing income or expense marker.';
        } elseif ($this->normalizeDirectionOrNull((string) $row['income_or_expense']) === null) {
            $reasons[] = 'The income or expense marker is invalid.';
        }

        return $reasons;
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)
            ->filter(fn (?string $value): bool => filled(trim((string) $value)))
            ->isEmpty();
    }

    private function normalizeHeader(string $value): string
    {
        return Str::lower(preg_replace('/[^a-z0-9]+/i', '', trim(str_replace("\u{FEFF}", '', $value))) ?? '');
    }

    private function parseDate(string $value): CarbonImmutable
    {
        $parsedDate = $this->parseDateOrNull($value);

        if ($parsedDate === null) {
            throw ValidationException::withMessages([
                'file' => sprintf('Unable to parse date value "%s".', $value),
            ]);
        }

        return $parsedDate;
    }

    private function parseDateOrNull(string $value): ?CarbonImmutable
    {
        $trimmedValue = trim($value);

        foreach (['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y'] as $format) {
            try {
                $parsedDate = CarbonImmutable::createFromFormat($format, $trimmedValue);

                if ($parsedDate !== false) {
                    return $parsedDate->startOfDay();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return CarbonImmutable::parse($trimmedValue)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAmount(string $value): float
    {
        $amount = $this->parseAmountOrNull($value);

        if ($amount === null) {
            throw ValidationException::withMessages([
                'file' => sprintf('Unable to parse amount value "%s".', $value),
            ]);
        }

        return $amount;
    }

    private function parseAmountOrNull(string $value): ?float
    {
        $trimmedValue = trim($value);
        $normalized = str_replace(['£', ',', ' '], '', $trimmedValue);

        if (str_starts_with($normalized, '(') && str_ends_with($normalized, ')')) {
            $normalized = '-'.trim($normalized, '()');
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function normalizeDirection(string $value): string
    {
        $direction = $this->normalizeDirectionOrNull($value);

        if ($direction === null) {
            throw ValidationException::withMessages([
                'file' => sprintf('Unable to parse income or expense value "%s".', $value),
            ]);
        }

        return $direction;
    }

    private function normalizeDirectionOrNull(string $value): ?string
    {
        return match (Str::lower(str_replace([' ', '-'], '', trim($value)))) {
            'income', 'turnover', 'revenue' => 'income',
            'expense', 'expenses', 'cost', 'outgoing' => 'expense',
            default => null,
        };
    }
}
