<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Str;

/**
 * Log an informational message to stderr.
 */
function info(string $message, string|int|float ...$args): void
{
    IO\write_error_line(
        namespace\styled('  info ', Ansi\foreground(Color\bright_blue()), Style\bold())
            . Str\format($message, ...$args),
    );
}
