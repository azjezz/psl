<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;
use Psl\Str;

use function putenv;

/**
 * Removes an environment variable from the environment of the currently running process.
 *
 * @throws Psl\Exception\InvariantViolationException If $key is empty, or contains an ASCII equals sign `=` or
 *                                                   the NUL character `\0`.
 */
function remove_var(string $key): void
{
    Psl\invariant(
        !Str\is_empty($key) && !Str\contains($key, '=') && !Str\contains($key, "\0"),
        'Invalid environment variable key provided.'
    );

    putenv($key);
}
