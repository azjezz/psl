<?php

declare(strict_types=1);

namespace Psl\Html;

use function htmlentities;

use const ENT_QUOTES;

/**
 * Convert all applicable characters to HTML entities.
 *
 * @param bool $doubleEncoding If set to false, this function will not
 *                              encode existing html entities.
 * @param Encoding $encoding defines character set used in conversion.
 *
 * @psalm-taint-escape html
 *
 * @pure
 */
function encode(string $html, bool $doubleEncoding = true, Encoding $encoding = Encoding::Utf8): string
{
    return htmlentities($html, ENT_QUOTES, $encoding->value, $doubleEncoding);
}
