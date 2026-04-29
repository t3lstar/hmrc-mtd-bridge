<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'report_type', 'category_type', 'sort_order'])]
class HmrcCategory extends Model
{
    /** @use HasFactory<\Database\Factories\HmrcCategoryFactory> */
    use HasFactory;

    public function categoryMappings(): HasMany
    {
        return $this->hasMany(CategoryMapping::class);
    }
}
