<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCategoryMappingRequest;
use App\Models\CategoryMapping;
use Illuminate\Http\RedirectResponse;

class CategoryMappingController extends Controller
{
    public function update(UpdateCategoryMappingRequest $request, CategoryMapping $categoryMapping): RedirectResponse
    {
        $validated = $request->validated();

        $categoryMapping->update([
            'hmrc_category_id' => $validated['hmrc_category_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'needs_review' => ! filled($validated['hmrc_category_id'] ?? null),
        ]);

        return back()->with('status', 'Category mapping updated.');
    }
}
