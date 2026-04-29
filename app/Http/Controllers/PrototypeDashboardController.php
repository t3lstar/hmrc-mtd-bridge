<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\HmrcCategory;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use App\Services\SummaryService;
use App\Services\TaxYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrototypeDashboardController extends Controller
{
    public function __invoke(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        $availableTaxYears = $summaryService->availableTaxYears();
        $selectedTaxYear = $request->integer('tax_year', $availableTaxYears->first());

        if (! $availableTaxYears->contains($selectedTaxYear)) {
            $selectedTaxYear = $availableTaxYears->first();
        }

        return view('prototype.dashboard', [
            'businesses' => Business::query()->orderBy('name')->get(),
            'hmrcCategories' => HmrcCategory::query()
                ->orderBy('report_type')
                ->orderBy('category_type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'categoryMappings' => CategoryMapping::query()
                ->with(['hmrcCategory'])
                ->withCount('importedTransactions')
                ->orderByDesc('needs_review')
                ->orderBy('freeagent_category')
                ->get(),
            'importBatches' => ImportBatch::query()
                ->with('business')
                ->latest('imported_at')
                ->take(8)
                ->get(),
            'auditRows' => ImportedTransaction::query()
                ->with(['business', 'categoryMapping.hmrcCategory'])
                ->where('tax_year_start', $selectedTaxYear)
                ->latest('transaction_date')
                ->paginate(25)
                ->withQueryString(),
            'selectedTaxYear' => $selectedTaxYear,
            'taxYearOptions' => $availableTaxYears,
            'taxYearLabel' => $taxYearService->label($selectedTaxYear),
            'taxYearPeriods' => $taxYearService->periodsForTaxYear($selectedTaxYear),
            'summary' => $summaryService->build($selectedTaxYear),
        ]);
    }
}
