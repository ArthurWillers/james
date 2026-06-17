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

    public static function formatDateTime(string|Carbon $date): string
    {
        return Carbon::parse($date)->format('d/m/Y \à\s H:i');
    }
}

if (! function_exists('formatDate')) {
    function formatDate($date)
    {
        return DateHelper::format($date);
    }
}

if (! function_exists('formatShort')) {
    function formatShort($date)
    {
        return DateHelper::formatShort($date);
    }
}

if (! function_exists('formatDateTime')) {
    function formatDateTime($date)
    {
        return DateHelper::formatDateTime($date);
    }
}

if (! function_exists('formatRelative')) {
    function formatRelative($date)
    {
        return DateHelper::formatRelative($date);
    }
}
