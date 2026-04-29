<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryMappingController;
use App\Http\Controllers\HmrcCategoryController;
use App\Http\Controllers\ImportBatchController;
use App\Http\Controllers\PrototypeDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', PrototypeDashboardController::class)->name('home');

Route::redirect('dashboard', '/')->name('dashboard');

Route::post('businesses', [BusinessController::class, 'store'])->name('businesses.store');
Route::patch('businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
Route::post('imports', [ImportBatchController::class, 'store'])->name('imports.store');
Route::patch('category-mappings/{categoryMapping}', [CategoryMappingController::class, 'update'])->name('category-mappings.update');
Route::post('hmrc-categories', [HmrcCategoryController::class, 'store'])->name('hmrc-categories.store');
Route::patch('hmrc-categories/{hmrcCategory}', [HmrcCategoryController::class, 'update'])->name('hmrc-categories.update');

require __DIR__.'/settings.php';
