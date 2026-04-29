<?php

namespace Database\Seeders;

use App\Models\HmrcCategory;
use Illuminate\Database\Seeder;

class HmrcCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'SE_TURNOVER', 'name' => 'Self-employment turnover', 'report_type' => 'self_employment', 'category_type' => 'income', 'sort_order' => 10],
            ['code' => 'SE_OTHER_INCOME', 'name' => 'Other self-employment income', 'report_type' => 'self_employment', 'category_type' => 'income', 'sort_order' => 20],
            ['code' => 'SE_COST_OF_GOODS', 'name' => 'Cost of goods bought for resale', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 30],
            ['code' => 'SE_CAR_TRAVEL', 'name' => 'Car, van and travel expenses', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 40],
            ['code' => 'SE_PREMISES', 'name' => 'Premises costs', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 50],
            ['code' => 'SE_ADMIN', 'name' => 'Office, property and equipment', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 60],
            ['code' => 'SE_ADVERTISING', 'name' => 'Advertising and marketing', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 70],
            ['code' => 'SE_FINANCE', 'name' => 'Bank and finance charges', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 80],
            ['code' => 'SE_PROFESSIONAL', 'name' => 'Professional fees', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 90],
            ['code' => 'SE_REPAIRS', 'name' => 'Repairs and maintenance', 'report_type' => 'self_employment', 'category_type' => 'expense', 'sort_order' => 100],
            ['code' => 'UKP_RENT', 'name' => 'UK property rental income', 'report_type' => 'uk_property', 'category_type' => 'income', 'sort_order' => 110],
            ['code' => 'UKP_OTHER_INCOME', 'name' => 'Other UK property income', 'report_type' => 'uk_property', 'category_type' => 'income', 'sort_order' => 120],
            ['code' => 'UKP_REPAIRS', 'name' => 'Property repairs and maintenance', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 130],
            ['code' => 'UKP_FINANCE', 'name' => 'Loan interest and finance costs', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 140],
            ['code' => 'UKP_LEGAL', 'name' => 'Legal, management and professional fees', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 150],
            ['code' => 'UKP_RATES', 'name' => 'Rates, council tax and ground rents', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 160],
            ['code' => 'UKP_INSURANCE', 'name' => 'Property insurance', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 170],
            ['code' => 'UKP_TRAVEL', 'name' => 'Property travel costs', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 180],
            ['code' => 'UKP_OTHER_EXPENSES', 'name' => 'Other allowable property expenses', 'report_type' => 'uk_property', 'category_type' => 'expense', 'sort_order' => 190],
        ];

        foreach ($categories as $category) {
            HmrcCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                $category,
            );
        }
    }
}
