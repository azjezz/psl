<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Str;

/**
 * Log a success message to stderr.
 */
function success(string $message, string|int|float ...$args): void
{
    IO\write_error_line(
        namespace\styled('  done ', Ansi\foreground(Color\bright_green()), Style\bold())
            . Str\format($message, ...$args),
    );
}
