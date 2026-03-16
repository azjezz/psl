<?php

declare(strict_types=1);

namespace Psl\Ansi;

use function preg_replace;

/**
 * Strips all ANSI escape sequences from the given text.
 *
 * @pure
 */
function strip(string $text): string
{
    return preg_replace('/\e(?:\[\??[0-9;]*[A-Za-z]|\][^\x07\e]*(?:\e\\\\|\x07))/', '', $text) ?? $text;
}
