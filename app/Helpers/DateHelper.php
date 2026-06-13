<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function format(string|Carbon $date): string
    {
        return Carbon::parse($date)->isoFormat('D [de] MMMM [de] YYYY');
    }

    public static function formatShort(string|Carbon $date): string
    {
        return Carbon::parse($date)->isoFormat('DD/MM/YYYY');
    }

    public static function formatRelative(string|Carbon $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }
}
