<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lookup_key', 'freeagent_category', 'hmrc_category_id', 'needs_review', 'notes'])]
class CategoryMapping extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryMappingFactory> */
    use HasFactory;

    public function hmrcCategory(): BelongsTo
    {
        return $this->belongsTo(HmrcCategory::class);
    }

    public function importedTransactions(): HasMany
    {
        return $this->hasMany(ImportedTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'needs_review' => 'boolean',
        ];
    }
}
