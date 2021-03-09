<?php

declare(strict_types=1);

namespace Psl\Html;

use Psl\Exception;
use Psl\Internal;

use function htmlspecialchars;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Convert special characters to HTML entities.
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
function encode_special_characters(string $html, bool $double_encoding = true, ?string $encoding = null): string
{
    $encoding = Internal\internal_encoding($encoding);

    return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, $encoding, $double_encoding);
}
