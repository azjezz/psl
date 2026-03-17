<?php

declare(strict_types=1);

namespace Psl\Env;

use function getenv;

/**
 * Returns an iterator of (variable, value) pairs of strings, for all the environment variables of the current process.
 *
 * @return array<string, string>
 */
function get_vars(): array
{
    return getenv();
}
