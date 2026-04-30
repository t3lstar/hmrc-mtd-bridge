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
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('freeagent_client_id')->nullable()->after('is_active');
            $table->text('freeagent_client_secret')->nullable()->after('freeagent_client_id');
            $table->text('freeagent_refresh_token')->nullable()->after('freeagent_client_secret');
            $table->text('freeagent_access_token')->nullable()->after('freeagent_refresh_token');
            $table->timestamp('freeagent_access_token_expires_at')->nullable()->after('freeagent_access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'freeagent_client_id',
                'freeagent_client_secret',
                'freeagent_refresh_token',
                'freeagent_access_token',
                'freeagent_access_token_expires_at',
            ]);
        });
    }
};
