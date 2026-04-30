<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'ownership_percentage',
    'is_active',
    'freeagent_client_id',
    'freeagent_client_secret',
    'freeagent_refresh_token',
    'freeagent_access_token',
    'freeagent_access_token_expires_at',
])]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class);
    }

    public function importedTransactions(): HasMany
    {
        return $this->hasMany(ImportedTransaction::class);
    }

    public function hasFreeAgentCredentials(): bool
    {
        return filled($this->freeagent_client_id)
            && filled($this->freeagent_client_secret)
            && filled($this->freeagent_refresh_token);
    }

    public function freeAgentAccessTokenHasExpired(?CarbonInterface $now = null): bool
    {
        if (blank($this->freeagent_access_token)) {
            return true;
        }

        $expiresAtValue = $this->getAttribute('freeagent_access_token_expires_at');

        if ($expiresAtValue === null) {
            return true;
        }

        $expiresAt = $this->asDateTime($expiresAtValue);

        return $expiresAt->lte(($now ?? now())->copy()->addMinute());
    }

    protected function casts(): array
    {
        return [
            'ownership_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'freeagent_client_secret' => 'encrypted',
            'freeagent_refresh_token' => 'encrypted',
            'freeagent_access_token' => 'encrypted',
            'freeagent_access_token_expires_at' => 'datetime',
        ];
    }
}
