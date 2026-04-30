<?php

namespace App\Models;

use Database\Factories\HmrcCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'report_type', 'category_type', 'sort_order'])]
class HmrcCategory extends Model
{
    /** @use HasFactory<HmrcCategoryFactory> */
    use HasFactory;

    public function categoryMappings(): HasMany
    {
        return $this->hasMany(CategoryMapping::class);
    }
}
