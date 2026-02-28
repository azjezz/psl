<?php

declare(strict_types=1);

namespace Psl\Ansi;

use Psl\Regex;

/**
 * Strips all ANSI escape sequences from the given text.
 *
 * @pure
 */
function strip(string $text): string
{
    return Regex\replace($text, '/\e(?:\[\??[0-9;]*[A-Za-z]|\][^\x07\e]*(?:\e\\\\|\x07))/', '');
}
