<?php

declare(strict_types=1);

namespace Psl\Html;

use function htmlspecialchars_decode;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Convert special HTML entities back to characters.
 *
 * @pure
 */
function decode_special_characters(string $html): string
{
    return htmlspecialchars_decode($html, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE);
}
