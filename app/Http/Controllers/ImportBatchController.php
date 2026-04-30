<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFreeAgentImportRequest;
use App\Http\Requests\StoreImportBatchRequest;
use App\Models\Business;
use App\Services\CsvImportService;
use App\Services\FreeAgentImportService;
use Illuminate\Http\RedirectResponse;

class ImportBatchController extends Controller
{
    public function store(StoreImportBatchRequest $request, CsvImportService $csvImportService): RedirectResponse
    {
        $business = Business::query()->findOrFail($request->integer('business_id'));
        $result = $csvImportService->import($business, $request->file('file'));

        return back()->with('import_result', [
            'business' => $business->name,
            'imported_rows' => $result['imported_rows'],
            'invalid_rows' => $result['invalid_rows'],
            'invalid_details' => array_slice($result['invalid_details'], 0, 10),
            'source' => 'CSV upload',
        ]);
    }

    public function storeFreeAgent(StoreFreeAgentImportRequest $request, FreeAgentImportService $freeAgentImportService): RedirectResponse
    {
        $business = Business::query()->findOrFail($request->integer('business_id'));
        $result = $freeAgentImportService->import(
            $business,
            $request->integer('tax_year_start'),
            $request->filled('quarter') ? $request->integer('quarter') : null,
        );

        return back()->with('import_result', [
            'business' => $business->name,
            'imported_rows' => $result['imported_rows'],
            'invalid_rows' => $result['invalid_rows'],
            'invalid_details' => $result['invalid_details'],
            'source' => 'FreeAgent API',
        ]);
    }
}
