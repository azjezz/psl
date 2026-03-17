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
    null|DateStyle $dateStyle = null,
    null|TimeStyle $timeStyle = null,
    null|FormatPattern|string $pattern = null,
    null|Timezone $timezone = null,
    null|Locale $locale = null,
): IntlDateFormatter {
    if ($pattern instanceof FormatPattern) {
        $pattern = $pattern->value;
    }

    $dateStyle ??= DateStyle::default();
    $timeStyle ??= TimeStyle::default();
    $locale ??= Locale::default();
    $timezone ??= Timezone::default();

    return new IntlDateFormatter(
        $locale->value,
        match ($dateStyle) {
            DateStyle::None => IntlDateFormatter::NONE,
            DateStyle::Short => IntlDateFormatter::SHORT,
            DateStyle::Medium => IntlDateFormatter::MEDIUM,
            // @codeCoverageIgnoreStart
            DateStyle::Long => IntlDateFormatter::LONG,
            DateStyle::Full => IntlDateFormatter::FULL,
            // @codeCoverageIgnoreEnd
        },
        match ($timeStyle) {
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
