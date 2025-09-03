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

    $string_length = length($string, $encoding);
    $break_length = length($break, $encoding);
    $result = '';
    /** @var int<0, max> $last_space */
    $last_start = 0;
    /** @var int<0, max> $last_space */
    $last_space = 0;
    for ($current = 0; $current < $string_length; ++$current) {
        $char = slice($string, $current, 1, $encoding);
        $possible_break = $char;
        if (1 !== $break_length) {
            $possible_break = slice($string, $current, $break_length, $encoding);
        }

        if ($possible_break === $break) {
            /** @var int<0, max> $slice_length */
            $slice_length = $current - $last_start + $break_length;
            $result .= slice($string, $last_start, $slice_length, $encoding);
            $current += $break_length - 1;
            $last_space = $current + 1;
            $last_start = $last_space;
            continue;
        }

        if (' ' === $char) {
            $length = $current - $last_start;
            if ($length >= $width) {
                $result .= slice($string, $last_start, $length, $encoding) . $break;
                $last_start = $current + 1;
            }

            $last_space = $current;
            continue;
        }

        $length = $current - $last_start;
        if ($length >= $width && $cut && $last_start >= $last_space) {
            $result .= slice($string, $last_start, $length, $encoding) . $break;
            $last_space = $current;
            $last_start = $last_space;
            continue;
        }

        if (($current - $last_start) >= $width && $last_start < $last_space) {
            /** @var int<0, max> $slice_length */
            $slice_length = $last_space - $last_start;
            $result .= slice($string, $last_start, $slice_length, $encoding) . $break;
            $last_start = ++$last_space;
        }
    }

    if ($last_start !== $current) {
        /** @var int<0, max> $slice_length */
        $slice_length = $current - $last_start;

        $result .= slice($string, $last_start, $slice_length, $encoding);
    }

    return $result;
}
