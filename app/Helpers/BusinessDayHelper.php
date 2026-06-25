<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BusinessDayHelper
{
    public static function holidaysForYear(int $year): array
    {
        return Cache::rememberForever("holidays:{$year}", function () use ($year) {
            try {
                $response = Http::timeout(5)->get("https://brasilapi.com.br/api/feriados/v1/{$year}");

                if ($response->successful()) {
                    return collect($response->json())->pluck('date')->toArray();
                }
            } catch (\Exception $e) {
                // Ignore network errors and return empty array
            }

            return [];
        });
    }

    public static function isHoliday(Carbon $date): bool
    {
        $holidays = self::holidaysForYear($date->year);

        return in_array($date->format('Y-m-d'), $holidays);
    }

    public static function isBusinessDay(Carbon $date): bool
    {
        return ! $date->isWeekend() && ! self::isHoliday($date);
    }

    public static function previousBusinessDay(Carbon $date): Carbon
    {
        $current = $date->copy();

        while (! self::isBusinessDay($current)) {
            $current->subDay();
        }

        return $current;
    }

    public static function nextBusinessDay(Carbon $date): Carbon
    {
        $current = $date->copy();

        while (! self::isBusinessDay($current)) {
            $current->addDay();
        }

        return $current;
    }
}
