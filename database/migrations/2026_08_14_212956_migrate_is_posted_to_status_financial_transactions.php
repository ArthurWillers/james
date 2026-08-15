<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Transfers the logical data from the old boolean is_posted column
     * to the new string status column.
     * is_posted = true  → status = 'posted'
     * is_posted = false → status = 'pending'
     */
    public function up(): void
    {
        DB::table('financial_transactions')
            ->whereNotNull('is_posted')
            ->update([
                'status' => DB::raw("CASE WHEN is_posted = true THEN 'posted' ELSE 'pending' END"),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * Restores is_posted from status: posted → true, anything else → false.
     */
    public function down(): void
    {
        DB::table('financial_transactions')
            ->whereNotNull('status')
            ->update([
                'is_posted' => DB::raw("CASE WHEN status = 'posted' THEN 1 ELSE 0 END"),
            ]);
    }
};
