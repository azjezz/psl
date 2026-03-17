<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\IO;
use Psl\Str;

/**
 * Log a shell command to stderr.
 */
function command(string $message, string|int|float ...$args): void
{
    $formatted = $args === [] ? $message : Str\format($message, ...$args);

    IO\write_error_line('%s', styled('       $ ', Ansi\foreground(Color\bright_black())) . $formatted);
}
