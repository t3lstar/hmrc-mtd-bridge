<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'import_batch_id',
    'business_id',
    'category_mapping_id',
    'transaction_date',
    'freeagent_category',
    'description',
    'amount',
    'normalized_amount',
    'income_or_expense',
    'tax_year_start',
    'tax_year_quarter',
    'raw_row',
])]
class ImportedTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\ImportedTransactionFactory> */
    use HasFactory;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function categoryMapping(): BelongsTo
    {
        return $this->belongsTo(CategoryMapping::class);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'normalized_amount' => 'decimal:2',
            'raw_row' => 'array',
        ];
    }
}
