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
        Schema::create('settlement_groups', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('total_amount', 10, 2);
            $table->date('date');
            $table->string('mode'); // 'equal' or 'exact'
            $table->foreignId('financial_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_groups');
    }
};
