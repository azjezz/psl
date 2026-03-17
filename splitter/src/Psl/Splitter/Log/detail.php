<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\IO;
use Psl\Str;

/**
 * Log a detail line to stderr (indented, no label).
 */
function detail(string $message, string|int|float ...$args): void
{
    IO\write_error_line('         ' . Str\format($message, ...$args));
}
