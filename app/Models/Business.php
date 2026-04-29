<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'ownership_percentage', 'is_active'])]
class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory;

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class);
    }

    public function importedTransactions(): HasMany
    {
        return $this->hasMany(ImportedTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'ownership_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
