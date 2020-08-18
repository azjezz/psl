<?php

declare(strict_types=1);

namespace Psl\Env;

use function chdir;
use Psl;

/**
 * Changes the current working directory to the specified path.
 *
 * @param string $directory
 *
 * @throws Psl\Exception\InvariantViolationException If the operation fails.
 */
function set_current_dir(string $directory): void
{
    Psl\invariant(chdir($directory), 'Unable to change directory');
}
