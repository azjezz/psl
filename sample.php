<?php

use Psl\DateTime;
use Psl\IO;
use Psl\Async;
use Psl\Locale;

use DateTime as NativeDateTime;
use DateTimeZone as NativeDateTimeZone;

require 'vendor/autoload.php';

Async\main(static function(): void {
    $someday = DateTime\DateTime::fromTimestamp(
        DateTime\Timestamp::fromRaw(1711846900),
        DateTime\Timezone::EuropeLondon,
    );

    IO\write_line('The offset of the timezone: %s', $someday->getTimezone()->getOffset($someday)->getTotalMinutes());
    IO\write_line('The location of the timezone: %s', json_encode($someday->getTimezone()->getLocation()));

    $today = DateTime\DateTime::now(DateTime\Timezone::EuropeLondon);
    $today_native = new NativeDateTime('now', new NativeDateTimeZone('Europe/London'));

    $that_day = $today->plusDays(24);
    $that_day_native = $today_native->modify('+24 days');

    var_dump($that_day->format(DateTime\DateFormat::Iso8601), $that_day_native->format(DateTimeInterface::ATOM));
    var_dump($that_day->getTimezone()->name, $that_day_native->getTimezone()->getName());
    var_dump($that_day->getTimezone()->getOffset($that_day), $that_day_native->getTimezone()->getOffset($that_day_native));

    $now = DateTime\DateTime::now(DateTime\Timezone::EuropeLondon);

    foreach (DateTime\DateFormat::cases() as $case) {
        IO\write_line('time: %s -> %s', $case->name, $now->format(format: $case, locale: Locale\Locale::English));
    }

    IO\write_line('The offset of the timezone: %s', $now->getTimezone()->getOffset($now)->toString());

    $after_4_months = $now->withMonth(4);
    IO\write_line('The offset of the timezone: %s', $after_4_months->getTimezone()->getOffset($after_4_months)->toString());

    $future = $now->withYear(2051)->withMonth(4)->withDay(2)->withHours(14)->withMinutes(51)->withSeconds(21)->withNanoseconds(124636);

    $now_timestamp = $now->getTimestamp();
    $future_timestamp = $future->getTimestamp();

    var_dump($now_timestamp->compare($future_timestamp), $future->isAfter($now), $now->isBeforeOrAtTheSameTime($future));

    IO\write_line('The offset of the future timezone: %s', $future->getTimezone()->getOffset($future)->toString());
    IO\write_line('Time: %s', $now->getTimestamp()->format($now->getTimezone(), locale: Locale\Locale::French));

    $now = DateTime\DateTime::now(DateTime\Timezone::EuropeLondon);
    IO\write_line('Time: %s', json_encode($now));
});
