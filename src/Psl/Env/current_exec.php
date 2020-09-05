<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function realpath;

/**
 * Returns the full filesystem path of the current running executable.
 *
 * @throws Psl\Exception\InvariantViolationException If unable to retrieve the current running executable.
 */
function current_exec(): string
{
    return realpath((string) $_SERVER['SCRIPT_NAME']);
}
