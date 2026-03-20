<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Str;

/**
 * Log a package step action to stderr.
 */
function step(string $package, string $action, string|int|float ...$args): void
{
    IO\write_error_line(
        namespace\styled('    -> ', Ansi\foreground(Color\bright_black()))
            . namespace\styled($package, Ansi\foreground(Color\bright_white()), Style\bold())
            . namespace\styled(' ' . Str\format($action, ...$args), Ansi\foreground(Color\bright_black())),
    );
}
