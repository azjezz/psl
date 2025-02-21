<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use IntlCalendar;
use Psl\DateTime\Timezone;

/**
 * @internal
 *
 * @psalm-mutation-free
 *
 * @psalm-suppress ImpureMethodCall - `IntlCalender::setDateTime()` is mutation free, as it performs a read-only operation.
 *
 * @infection-ignore-all
 *
 * @mago-ignore best-practices/no-else-clause
 */
function create_intl_calendar_from_date_time(
    Timezone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
): IntlCalendar {
    /**
     * @var IntlCalendar $calendar
     */
    $calendar = IntlCalendar::createInstance(to_intl_timezone($timezone));

    if (PHP_VERSION_ID >= 80300) {
        $calendar->setDateTime($year, $month - 1, $day, $hours, $minutes, $seconds);
    } else {
        // @codeCoverageIgnoreStart
        $calendar->set($year, $month - 1, $day, $hours, $minutes, $seconds);
        // @codeCoverageIgnoreEnd
    }

    return $calendar;
}
