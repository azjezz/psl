<?php

declare(strict_types=1);

namespace Psl\Html;

use Psl\Exception;
use Psl\Internal;

use function html_entity_decode;

use const ENT_QUOTES;

/**
 * Convert HTML entities to their corresponding characters.
 *
 * @param string $encoding defines character set used in conversion.
 *
 * @throws Exception\InvariantViolationException If $encoding is invalid.
 *
 * @pure
 */
function decode(string $html, ?string $encoding = null): string
{
    $encoding = Internal\internal_encoding($encoding);

    return html_entity_decode($html, ENT_QUOTES, $encoding);
}
