<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryMappingController;
use App\Http\Controllers\HmrcCategoryController;
use App\Http\Controllers\ImportBatchController;
use App\Http\Controllers\PrototypeDashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::controller(PrototypeDashboardController::class)->group(function (): void {
    Route::get('dashboard', 'overview')->name('dashboard');
    Route::get('dashboard/businesses', 'businesses')->name('dashboard.businesses');
    Route::get('dashboard/imports', 'imports')->name('dashboard.imports');
    Route::get('dashboard/mappings', 'mappings')->name('dashboard.mappings');
    Route::get('dashboard/categories', 'categories')->name('dashboard.categories');
    Route::get('dashboard/audit', 'audit')->name('dashboard.audit');
});

Route::post('businesses', [BusinessController::class, 'store'])->name('businesses.store');
Route::patch('businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
Route::patch('businesses/{business}/freeagent-credentials', [BusinessController::class, 'updateFreeAgentCredentials'])->name('businesses.freeagent-credentials.update');
Route::post('imports', [ImportBatchController::class, 'store'])->name('imports.store');
Route::post('imports/freeagent', [ImportBatchController::class, 'storeFreeAgent'])->name('imports.freeagent.store');
Route::patch('category-mappings/{categoryMapping}', [CategoryMappingController::class, 'update'])->name('category-mappings.update');
Route::post('hmrc-categories', [HmrcCategoryController::class, 'store'])->name('hmrc-categories.store');
Route::patch('hmrc-categories/{hmrcCategory}', [HmrcCategoryController::class, 'update'])->name('hmrc-categories.update');

require __DIR__.'/settings.php';
