<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('financial_transactions')
            ->select('financial_recurrence_id', 'date')
            ->whereNotNull('financial_recurrence_id')
            ->groupBy('financial_recurrence_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $occurrenceIds = DB::table('financial_transactions')
                ->where('financial_recurrence_id', $duplicate->financial_recurrence_id)
                ->where('date', $duplicate->date)
                ->orderBy('id')
                ->pluck('id');

            DB::table('financial_transactions')
                ->whereIn('id', $occurrenceIds->skip(1))
                ->update(['financial_recurrence_id' => null]);
        }
    }

    public function down(): void
    {
        // Duplicate recurrence associations cannot be restored safely.
    }
};
