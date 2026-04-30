<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessFreeAgentCredentialsRequest;
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

    public function updateFreeAgentCredentials(UpdateBusinessFreeAgentCredentialsRequest $request, Business $business): RedirectResponse
    {
        $validated = $request->validated();
        $allBlank = collect($validated)->filter(fn ($value) => filled($value))->isEmpty();

        $business->forceFill([
            'freeagent_client_id' => $allBlank ? null : $validated['freeagent_client_id'],
            'freeagent_client_secret' => $allBlank ? null : $validated['freeagent_client_secret'],
            'freeagent_refresh_token' => $allBlank ? null : $validated['freeagent_refresh_token'],
            'freeagent_access_token' => $allBlank ? null : ($validated['freeagent_access_token'] ?? null),
            'freeagent_access_token_expires_at' => null,
        ])->save();

        return back()->with('status', 'FreeAgent credentials updated.');
    }
}
