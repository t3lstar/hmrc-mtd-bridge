<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class TaxYearService
{
    public function currentTaxYearStart(?CarbonInterface $date = null): int
    {
        return $this->taxYearStart($date ?? CarbonImmutable::now());
    }

    public function taxYearStart(CarbonInterface|string $date): int
    {
        $parsedDate = $this->parseDate($date);
        $taxYearStart = CarbonImmutable::create($parsedDate->year, 4, 6, 0, 0, 0, $parsedDate->timezone);

        return $parsedDate->lt($taxYearStart) ? $parsedDate->year - 1 : $parsedDate->year;
    }

    public function quarterFor(CarbonInterface|string $date): int
    {
        $parsedDate = $this->parseDate($date);
        $taxYearStart = $this->taxYearStart($parsedDate);
        $periods = $this->periodsForTaxYear($taxYearStart);

        foreach ($periods as $period) {
            $periodStart = CarbonImmutable::parse($period['start']);
            $periodEnd = CarbonImmutable::parse($period['end']);

            if ($parsedDate->betweenIncluded($periodStart, $periodEnd)) {
                return $period['number'];
            }
        }

        return 4;
    }

    /**
     * @return array<int, array{number:int,label:string,start:string,end:string}>
     */
    public function periodsForTaxYear(int $taxYearStart): array
    {
        $start = CarbonImmutable::create($taxYearStart, 4, 6, 0, 0, 0);

        return [
            [
                'number' => 1,
                'label' => 'Q1: 6 Apr - 5 Jul',
                'start' => $start->format('Y-m-d'),
                'end' => $start->addMonths(3)->subDay()->format('Y-m-d'),
            ],
            [
                'number' => 2,
                'label' => 'Q2: 6 Jul - 5 Oct',
                'start' => $start->addMonths(3)->format('Y-m-d'),
                'end' => $start->addMonths(6)->subDay()->format('Y-m-d'),
            ],
            [
                'number' => 3,
                'label' => 'Q3: 6 Oct - 5 Jan',
                'start' => $start->addMonths(6)->format('Y-m-d'),
                'end' => $start->addMonths(9)->subDay()->format('Y-m-d'),
            ],
            [
                'number' => 4,
                'label' => 'Q4: 6 Jan - 5 Apr',
                'start' => $start->addMonths(9)->format('Y-m-d'),
                'end' => $start->addYear()->subDay()->format('Y-m-d'),
            ],
        ];
    }

    public function label(int $taxYearStart): string
    {
        return sprintf('%d/%02d', $taxYearStart, ($taxYearStart + 1) % 100);
    }

    private function parseDate(CarbonInterface|string $date): CarbonImmutable
    {
        if ($date instanceof CarbonInterface) {
            return CarbonImmutable::instance($date)->startOfDay();
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }
}
