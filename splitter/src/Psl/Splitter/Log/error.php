<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Str;

/**
 * Log an error message to stderr.
 */
function error(string $message, string|int|float ...$args): void
{
    IO\write_error_line(
        styled(' error ', Ansi\foreground(Color\bright_red()), Style\bold()) . Str\format($message, ...$args),
    );
}
