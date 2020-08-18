<?php

declare(strict_types=1);

namespace Psl\Env;

use function getenv;
use Psl;
use Psl\Type;

/**
 * Returns an iterator of (variable, value) pairs of strings, for all the environment variables of the current process.
 *
 * @psalm-return array<string, string>
 */
function get_vars(): array
{
    return getenv();
}
