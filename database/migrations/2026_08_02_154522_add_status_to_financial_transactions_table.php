<?php

use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('status')->default(TransactionStatus::Pending->value)->after('date');
        });

        DB::table('financial_transactions')
            ->where('is_posted', true)
            ->update(['status' => TransactionStatus::Posted->value]);

        DB::table('financial_transactions')
            ->where('is_posted', false)
            ->update(['status' => TransactionStatus::Pending->value]);

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn('is_posted');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->boolean('is_posted')->default(true)->after('date');
        });

        DB::table('financial_transactions')
            ->where('status', TransactionStatus::Posted->value)
            ->update(['is_posted' => true]);

        DB::table('financial_transactions')
            ->where('status', '!=', TransactionStatus::Posted->value)
            ->update(['is_posted' => false]);

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
