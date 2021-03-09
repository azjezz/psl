<?php

declare(strict_types=1);

namespace Psl\Html;

use Psl\Exception;
use Psl\Internal;

use function htmlentities;

use const ENT_QUOTES;

/**
 * Convert all applicable characters to HTML entities.
 *
 * @param bool $double_encoding If set to false, this function will not
 *                              encode existing html entities.
 * @param string $encoding defines character set used in conversion.
 *
 * @throws Exception\InvariantViolationException If $encoding is invalid.
 *
 * @psalm-taint-escape html
 *
 * @pure
 */
function encode(string $html, bool $double_encoding = true, ?string $encoding = null): string
{
    $encoding = Internal\internal_encoding($encoding);

    return htmlentities($html, ENT_QUOTES, $encoding, $double_encoding);
}
