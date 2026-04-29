<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;

class BusinessController extends Controller
{
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        Business::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('status', 'Business saved.');
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $business->update($request->validated());

        return back()->with('status', 'Business updated.');
    }
}
