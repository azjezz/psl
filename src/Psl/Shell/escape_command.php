<?php

declare(strict_types=1);

namespace Psl\Shell;

use function escapeshellcmd;

/**
 * Escape shell metacharacters.
 *
 * @psalm-taint-escape shell
 */
function escape_command(string $argument): string
{
    return escapeshellcmd($argument);
}
