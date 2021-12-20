<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function chdir;

/**
 * Changes the current working directory to the specified path.
 *
 * @throws Psl\Exception\InvariantViolationException If the operation fails.
 */
function set_current_dir(string $directory): void
{
    if (!@chdir($directory)) {
        Psl\invariant_violation('Unable to change directory');
    }
}
