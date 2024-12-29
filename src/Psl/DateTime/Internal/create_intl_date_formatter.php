<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use IntlDateFormatter;
use Psl\DateTime\DateStyle;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\TimeStyle;
use Psl\DateTime\Timezone;
use Psl\Locale\Locale;

/**
 * @internal
 *
 * @psalm-mutation-free
 */
function create_intl_date_formatter(
    null|DateStyle $date_style = null,
    null|TimeStyle $time_style = null,
    null|FormatPattern|string $pattern = null,
    null|Timezone $timezone = null,
    null|Locale $locale = null,
): IntlDateFormatter {
    if ($pattern instanceof FormatPattern) {
        $pattern = $pattern->value;
    }

    $date_style ??= DateStyle::default();
    $time_style ??= TimeStyle::default();
    $locale ??= Locale::default();
    $timezone ??= Timezone::default();

    return new IntlDateFormatter(
        $locale->value,
        match ($date_style) {
            DateStyle::None => IntlDateFormatter::NONE,
            DateStyle::Short => IntlDateFormatter::SHORT,
            DateStyle::Medium => IntlDateFormatter::MEDIUM,
            DateStyle::Long => IntlDateFormatter::LONG,
            DateStyle::Full => IntlDateFormatter::FULL,
        },
        match ($time_style) {
            TimeStyle::None => IntlDateFormatter::NONE,
            TimeStyle::Short => IntlDateFormatter::SHORT,
            TimeStyle::Medium => IntlDateFormatter::MEDIUM,
            TimeStyle::Long => IntlDateFormatter::LONG,
            TimeStyle::Full => IntlDateFormatter::FULL,
        },
        namespace\to_intl_timezone($timezone),
        IntlDateFormatter::GREGORIAN,
        $pattern,
    );
}
