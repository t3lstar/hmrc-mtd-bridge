<?php

namespace App\Models;

use Database\Factories\ImportErrorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['import_batch_id', 'row_number', 'raw_row', 'reasons'])]
class ImportError extends Model
{
    /** @use HasFactory<ImportErrorFactory> */
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
