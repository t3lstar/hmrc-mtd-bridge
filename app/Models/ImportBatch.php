<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'business_id',
    'original_filename',
    'stored_filename',
    'imported_at',
    'total_rows',
    'valid_rows',
    'invalid_rows',
])]
class ImportBatch extends Model
{
    /** @use HasFactory<\Database\Factories\ImportBatchFactory> */
    use HasFactory;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function importedTransactions(): HasMany
    {
        return $this->hasMany(ImportedTransaction::class);
    }

    public function importErrors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
        ];
    }
}
