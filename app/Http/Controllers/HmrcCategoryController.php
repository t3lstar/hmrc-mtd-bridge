<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHmrcCategoryRequest;
use App\Http\Requests\UpdateHmrcCategoryRequest;
use App\Models\HmrcCategory;
use Illuminate\Http\RedirectResponse;

class HmrcCategoryController extends Controller
{
    public function store(StoreHmrcCategoryRequest $request): RedirectResponse
    {
        HmrcCategory::query()->create($request->validated());

        return back()->with('status', 'HMRC category created.');
    }

    public function update(UpdateHmrcCategoryRequest $request, HmrcCategory $hmrcCategory): RedirectResponse
    {
        $hmrcCategory->update($request->validated());

        return back()->with('status', 'HMRC category updated.');
    }
}
