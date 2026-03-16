<?php

declare(strict_types=1);

namespace Psl\DateTime\Internal;

use Psl\DateTime\DateStyle;
use Psl\DateTime\Exception\ParserException;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\TimeStyle;
use Psl\DateTime\Timezone;
use Psl\Locale\Locale;

use function sprintf;

/**
 * @internal
 *
 * @psalm-mutation-free
 *
 * @throws ParserException
 */
function parse(
    string $rawString,
    null|DateStyle $dateStyle = null,
    null|TimeStyle $timeStyle = null,
    null|FormatPattern|string $pattern = null,
    null|Timezone $timezone = null,
    null|Locale $locale = null,
): int {
    $formatter = namespace\create_intl_date_formatter($dateStyle, $timeStyle, $pattern, $timezone, $locale);

    $timestamp = $formatter->parse($rawString);
    if (false === $timestamp) {
        // Only show pattern in the exception if it was provided.
        if (null !== $pattern) {
            $formatterPattern = $pattern instanceof FormatPattern ? $pattern->value : $pattern;

            throw new ParserException(sprintf(
                'Unable to interpret \'%s\' as a valid date/time using pattern \'%s\'.',
                $rawString,
                $formatterPattern,
            ));
        }

        throw new ParserException("Unable to interpret '{$rawString}' as a valid date/time.");
    }

    return (int) $timestamp;
}
