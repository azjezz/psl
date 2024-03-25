<?php

declare(strict_types=1);

namespace Psl\Html;

use function html_entity_decode;

use const ENT_QUOTES;

/**
 * Convert HTML entities to their corresponding characters.
 *
 * @param Encoding $encoding defines character set used in conversion.
 *
 * @pure
 */
function decode(string $html, Encoding $encoding = Encoding::Utf8): string
{
    return html_entity_decode($html, ENT_QUOTES, $encoding->value);
}
