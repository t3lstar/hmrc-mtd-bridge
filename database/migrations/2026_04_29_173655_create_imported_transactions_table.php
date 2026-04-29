<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('imported_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_mapping_id')->nullable()->constrained()->nullOnDelete();
            $table->date('transaction_date');
            $table->string('freeagent_category');
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('normalized_amount', 12, 2);
            $table->string('income_or_expense');
            $table->unsignedSmallInteger('tax_year_start');
            $table->unsignedTinyInteger('tax_year_quarter');
            $table->json('raw_row')->nullable();
            $table->timestamps();

            $table->index(['tax_year_start', 'tax_year_quarter']);
            $table->index(['business_id', 'tax_year_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_transactions');
    }
};
