<?php

namespace Database\Seeders;

use App\Models\CategoryMapping;
use App\Models\HmrcCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = HmrcCategory::query()
            ->pluck('id', 'code');

        $mappings = [
            'Sales' => 'SE_TURNOVER',
            'Other Income' => 'SE_OTHER_INCOME',
            'Cost of Sales' => 'SE_COST_OF_GOODS',
            'Travel' => 'SE_CAR_TRAVEL',
            'Office Costs' => 'SE_ADMIN',
            'Advertising & Marketing' => 'SE_ADVERTISING',
            'Bank Charges' => 'SE_FINANCE',
            'Professional Fees' => 'SE_PROFESSIONAL',
            'Repairs & Maintenance' => 'SE_REPAIRS',
            'Rent Received' => 'UKP_RENT',
            'Property Repairs' => 'UKP_REPAIRS',
            'Mortgage Interest' => 'UKP_FINANCE',
            'Insurance' => 'UKP_INSURANCE',
            'Property Travel' => 'UKP_TRAVEL',
        ];

        foreach ($mappings as $freeagentCategory => $categoryCode) {
            CategoryMapping::query()->updateOrCreate(
                ['lookup_key' => Str::lower(trim($freeagentCategory))],
                [
                    'freeagent_category' => $freeagentCategory,
                    'hmrc_category_id' => $codes[$categoryCode] ?? null,
                    'needs_review' => false,
                    'notes' => 'Seeded starter mapping for the prototype.',
                ],
            );
        }
    }
}
