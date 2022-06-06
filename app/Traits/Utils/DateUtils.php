<?php

namespace App\Traits\Utils;

use Carbon\Carbon;
use DateTime;

trait DateUtils
{

    function toTimeZone(string $date, string $dateTimeZone, $toTimeZone): Carbon
    {
        $result = Carbon::createFromFormat('Y-m-d H:i:s', $date, $dateTimeZone);
        $result->setTimezone($toTimeZone);

        return $result;
    }

    function toUtc(string $date, $timeZone): Carbon
    {
        return $this->toTimeZone($date, $timeZone, 'UTC');
    }


    function toNigerianTimeZone(string $date, string $fromTimeZone): Carbon
    {
        return $this->toTimeZone($date, $fromTimeZone, 'Africa/Lagos');
    }

    function toUtcFromNigeriaTimeZone(string $date): Carbon
    {
        return $this->toUtc($date, 'Africa/Lagos');
    }

    function toNigeriaTimeZoneFromUtc(string $date): Carbon
    {
        return $this->toNigerianTimeZone($date, 'UTC');
    }

    function toTimestamp(string $data): int
    {
        return (int) (Carbon::parse($data)->timestamp . str_pad(Carbon::parse($data)->milli, 3, '0', STR_PAD_LEFT));
    }
}
