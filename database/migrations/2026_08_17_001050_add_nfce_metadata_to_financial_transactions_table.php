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
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('nfce_access_key')->nullable()->unique();
            $table->string('nfce_provider')->nullable();
            $table->string('nfce_uf')->nullable();
            $table->string('nfce_source_endpoint')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropUnique(['nfce_access_key']);
            $table->dropColumn([
                'nfce_access_key',
                'nfce_provider',
                'nfce_uf',
                'nfce_source_endpoint',
            ]);
        });
    }
};
