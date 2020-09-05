<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function getenv;

/**
 * Returns an iterator of (variable, value) pairs of strings, for all the environment variables of the current process.
 *
 * @psalm-return array<string, string>
 */
function get_vars(): array
{
    return getenv();
}
