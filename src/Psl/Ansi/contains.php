<?php

declare(strict_types=1);

namespace Psl\Ansi;

use function preg_match;

/**
 * Checks whether the given text contains any ANSI escape sequences.
 *
 * @pure
 */
function contains(string $text): bool
{
    return preg_match('/\e(?:\[\??[0-9;]*[A-Za-z]|\][^\x07\e]*(?:\e\\\\|\x07))/', $text) === 1;
}
