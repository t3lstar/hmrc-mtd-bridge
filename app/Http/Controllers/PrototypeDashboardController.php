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
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PrototypeDashboardController extends Controller
{
    public function overview(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);

        return view('prototype.dashboard', [
            'importBatches' => ImportBatch::query()
                ->with('business')
                ->latest('imported_at')
                ->take(8)
                ->get(),
            'businesses' => Business::query()->orderBy('name')->get(),
            'recentAuditRows' => ImportedTransaction::query()
                ->with(['business', 'categoryMapping.hmrcCategory'])
                ->where('tax_year_start', $selectedTaxYear)
                ->latest('transaction_date')
                ->take(8)
                ->get(),
            'taxYearPeriods' => $taxYearService->periodsForTaxYear($selectedTaxYear),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    public function businesses(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);

        return view('prototype.businesses', [
            'businesses' => Business::query()->orderBy('name')->get(),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    public function imports(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);
        $currentTaxYearStart = $taxYearService->currentTaxYearStart();

        return view('prototype.imports', [
            'businesses' => Business::query()->orderBy('name')->get(),
            'importBatches' => ImportBatch::query()
                ->with('business')
                ->latest('imported_at')
                ->take(20)
                ->get(),
            'importTaxYearOptions' => collect(range($currentTaxYearStart - 3, $currentTaxYearStart + 1))
                ->sortDesc()
                ->values(),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    public function mappings(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);

        return view('prototype.mappings', [
            'hmrcCategories' => $this->hmrcCategories(),
            'categoryMappings' => CategoryMapping::query()
                ->with(['hmrcCategory'])
                ->withCount('importedTransactions')
                ->orderByDesc('needs_review')
                ->orderBy('freeagent_category')
                ->get(),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    public function categories(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);

        return view('prototype.categories', [
            'hmrcCategories' => $this->hmrcCategories(),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    public function audit(Request $request, SummaryService $summaryService, TaxYearService $taxYearService): View
    {
        [$selectedTaxYear, $availableTaxYears] = $this->resolveSelectedTaxYear($request, $summaryService);
        $summary = $summaryService->build($selectedTaxYear);

        return view('prototype.audit', [
            'auditRows' => ImportedTransaction::query()
                ->with(['business', 'categoryMapping.hmrcCategory'])
                ->where('tax_year_start', $selectedTaxYear)
                ->latest('transaction_date')
                ->paginate(25)
                ->withQueryString(),
        ] + $this->sharedViewData($selectedTaxYear, $availableTaxYears, $summary, $taxYearService));
    }

    /**
     * @return array{0:int,1:Collection<int, int>}
     */
    private function resolveSelectedTaxYear(Request $request, SummaryService $summaryService): array
    {
        $availableTaxYears = $summaryService->availableTaxYears();
        $selectedTaxYear = $request->integer('tax_year', $availableTaxYears->first());

        if (! $availableTaxYears->contains($selectedTaxYear)) {
            $selectedTaxYear = $availableTaxYears->first();
        }

        return [$selectedTaxYear, $availableTaxYears];
    }

    /**
     * @param  Collection<int, int>  $availableTaxYears
     * @param  array{
     *      has_transactions: bool,
     *      submission_ready: bool,
     *      unmapped_count: int,
     *      combined: array<int, array{label:string,income:float,expense:float,net:float}>,
     *      businesses: array<int, array{business: Business, quarters: array<int, array{label:string,income:float,expense:float,net:float}>, year: array{income:float,expense:float,net:float}}>,
     *      categories: array<int, array{label:string,report_type:string,category_type:string,income:float,expense:float,net:float,needs_review:bool}>,
     *      year: array{income:float,expense:float,net:float},
     *      transaction_count: int
     * }  $summary
     * @return array{
     *      selectedTaxYear: int,
     *      taxYearOptions: Collection<int, int>,
     *      taxYearLabel: string,
     *      summary: array{
     *          has_transactions: bool,
     *          submission_ready: bool,
     *          unmapped_count: int,
     *          combined: array<int, array{label:string,income:float,expense:float,net:float}>,
     *          businesses: array<int, array{business: Business, quarters: array<int, array{label:string,income:float,expense:float,net:float}>, year: array{income:float,expense:float,net:float}}>,
     *          categories: array<int, array{label:string,report_type:string,category_type:string,income:float,expense:float,net:float,needs_review:bool}>,
     *          year: array{income:float,expense:float,net:float},
     *          transaction_count: int
     *      }
     * }
     */
    private function sharedViewData(int $selectedTaxYear, Collection $availableTaxYears, array $summary, TaxYearService $taxYearService): array
    {
        return [
            'selectedTaxYear' => $selectedTaxYear,
            'taxYearOptions' => $availableTaxYears,
            'taxYearLabel' => $taxYearService->label($selectedTaxYear),
            'summary' => $summary,
        ];
    }

    private function hmrcCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return HmrcCategory::query()
            ->orderBy('report_type')
            ->orderBy('category_type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
