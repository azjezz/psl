<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\DateStyle;
use Psl\DateTime\Exception\ParserException;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\TimeStyle;
use Psl\DateTime\Timezone;
use Psl\Locale\Locale;
use Psl\Str;

/**
 * @internal
 *
 * @psalm-mutation-free
 *
 * @throws ParserException
 *
 * @mago-ignore best-practices/no-boolean-literal-comparison
 */
function parse(
    string $raw_string,
    null|DateStyle $date_style = null,
    null|TimeStyle $time_style = null,
    null|FormatPattern|string $pattern = null,
    null|Timezone $timezone = null,
    null|Locale $locale = null,
): int {
    $formatter = namespace\create_intl_date_formatter($date_style, $time_style, $pattern, $timezone, $locale);

    /** @psalm-suppress ImpureMethodCall */
    $timestamp = $formatter->parse($raw_string);
    if ($timestamp === false) {
        // Only show pattern in the exception if it was provided.
        if (null !== $pattern) {
            $formatter_pattern = ($pattern instanceof FormatPattern) ? $pattern->value : $pattern;

            throw new ParserException(Str\format(
                'Unable to interpret \'%s\' as a valid date/time using pattern \'%s\'.',
                $raw_string,
                $formatter_pattern,
            ));
        }

        throw new ParserException("Unable to interpret '$raw_string' as a valid date/time.");
    }

    return (int) $timestamp;
}
