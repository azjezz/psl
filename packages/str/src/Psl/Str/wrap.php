<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Wraps a string to a given number of characters.
 *
 * @param int<0, max> $width the width at which the string is wrapped.
 * @param non-empty-string $break the line is broken using the optional break parameter
 * @param bool $cut If the cut is set to true, the string is always wrapped at or before the specified width.
 *                  so if you have a word that is larger than the given width, it is broken apart.
 *
 * @throws Exception\LogicException If $width is 0 and $cut is set to true.
 *
 * @return string the given string wrapped at the specified column
 *
 * @pure
 */
function wrap(
    string $string,
    int $width = 75,
    string $break = "\n",
    bool $cut = false,
    Encoding $encoding = Encoding::Utf8,
): string {
    if ('' === $string) {
        return '';
    }

    if (0 === $width && $cut) {
        throw new Exception\LogicException('Cannot force cut when width is zero.');
    }

    $stringLength = namespace\length($string, $encoding);
    $breakLength = namespace\length($break, $encoding);
    $result = '';
    /** @var int<0, max> $lastSpace */
    $lastStart = 0;
    /** @var int<0, max> $lastSpace */
    $lastSpace = 0;
    for ($current = 0; $current < $stringLength; ++$current) {
        $char = namespace\slice($string, $current, 1, $encoding);
        $possibleBreak = $char;
        if (1 !== $breakLength) {
            $possibleBreak = namespace\slice($string, $current, $breakLength, $encoding);
        }

        if ($possibleBreak === $break) {
            /** @var int<0, max> $sliceLength */
            $sliceLength = $current - $lastStart + $breakLength;
            $result .= namespace\slice($string, $lastStart, $sliceLength, $encoding);
            $current += $breakLength - 1;
            $lastSpace = $current + 1;
            $lastStart = $lastSpace;
            continue;
        }

        if (' ' === $char) {
            $length = $current - $lastStart;
            if ($length >= $width) {
                $result .= namespace\slice($string, $lastStart, $length, $encoding) . $break;
                $lastStart = $current + 1;
            }

            $lastSpace = $current;
            continue;
        }

        $length = $current - $lastStart;
        if ($length >= $width && $cut && $lastStart >= $lastSpace) {
            $result .= namespace\slice($string, $lastStart, $length, $encoding) . $break;
            $lastSpace = $current;
            $lastStart = $lastSpace;
            continue;
        }

        if (($current - $lastStart) >= $width && $lastStart < $lastSpace) {
            /** @var int<0, max> $sliceLength */
            $sliceLength = $lastSpace - $lastStart;
            $result .= namespace\slice($string, $lastStart, $sliceLength, $encoding) . $break;
            $lastStart = ++$lastSpace;
        }
    }

    if ($lastStart !== $current) {
        /** @var int<0, max> $sliceLength */
        $sliceLength = $current - $lastStart;

        $result .= namespace\slice($string, $lastStart, $sliceLength, $encoding);
    }

    return $result;
}
