<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Wraps a string to a given number of characters.
 *
 * @param string $break the line is broken using the optional break parameter
 * @param bool   $cut   If the cut is set to true, the string is
 *                      always wrapped at or before the specified width. So if you have
 *                      a word that is larger than the given width, it is broken apart.
 *
 * @return string the given string wrapped at the specified column
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $break is empty, or $width is 0 and $cut is set to true.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function wrap(
    string $string,
    int $width = 75,
    string $break = "\n",
    bool $cut = false,
    ?string $encoding = null
): string {
    if ('' === $string) {
        return '';
    }

    Psl\invariant('' !== $break, 'Break string cannot be empty.');
    Psl\invariant(0 !== $width || !$cut, 'Cannot force cut when width is zero.');

    $stringLength = length($string, $encoding);
    $breakLength  = length($break, $encoding);
    $result       = '';
    $lastStart    = $lastSpace = 0;
    for ($current = 0; $current < $stringLength; ++$current) {
        $char          = slice($string, $current, 1, $encoding);
        $possibleBreak = $char;
        if (1 !== $breakLength) {
            $possibleBreak = slice($string, $current, $breakLength, $encoding);
        }

        if ($possibleBreak === $break) {
            $result   .= slice($string, $lastStart, $current - $lastStart + $breakLength, $encoding);
            $current  += $breakLength - 1;
            $lastStart = $lastSpace = $current + 1;
            continue;
        }

        if (' ' === $char) {
            if ($current - $lastStart >= $width) {
                $result   .= slice($string, $lastStart, $current - $lastStart, $encoding) . $break;
                $lastStart = $current + 1;
            }
            $lastSpace = $current;
            continue;
        }

        if ($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace) {
            $result   .= slice($string, $lastStart, $current - $lastStart, $encoding) . $break;
            $lastStart = $lastSpace = $current;
            continue;
        }

        if ($current - $lastStart >= $width && $lastStart < $lastSpace) {
            $result   .= slice($string, $lastStart, $lastSpace - $lastStart, $encoding) . $break;
            $lastStart = ++$lastSpace;
            continue;
        }
    }

    if ($lastStart !== $current) {
        $result .= slice($string, $lastStart, $current - $lastStart, $encoding);
    }

    return $result;
}
