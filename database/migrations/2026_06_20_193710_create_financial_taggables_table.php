<?php

use App\Models\FinancialTag;
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
        Schema::create('financial_taggables', function (Blueprint $table) {
            $table->foreignIdFor(FinancialTag::class)->constrained()->cascadeOnDelete();
            $table->morphs('financial_taggable');
            $table->primary(
                ['financial_tag_id', 'financial_taggable_id', 'financial_taggable_type'],
                'financial_taggables_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_taggables');
    }
};
