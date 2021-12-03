<?php

declare(strict_types=1);

namespace Psl\Html;

use function htmlspecialchars;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Convert special characters to HTML entities.
 *
 * @param bool $double_encoding If set to false, this function will not
 *                              encode existing html entities.
 * @param Encoding $encoding defines character set used in conversion.
 *
 * @psalm-taint-escape html
 *
 * @pure
 */
function encode_special_characters(string $html, bool $double_encoding = true, Encoding $encoding = Encoding::UTF_8): string
{
    return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, $encoding->value, $double_encoding);
}
