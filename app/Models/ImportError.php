<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['import_batch_id', 'row_number', 'raw_row', 'reasons'])]
class ImportError extends Model
{
    /** @use HasFactory<\Database\Factories\ImportErrorFactory> */
    use HasFactory;

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    protected function casts(): array
    {
        return [
            'raw_row' => 'array',
            'reasons' => 'array',
        ];
    }
}
