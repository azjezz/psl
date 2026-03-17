<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function putenv;
use function str_contains;

/**
 * Removes an environment variable from the environment of the currently running process.
 *
 * @param non-empty-string $key
 *
 * @throws Psl\Exception\InvariantViolationException If contains an ASCII equals sign `=` or, the NUL character `\0`.
 */
function remove_var(string $key): void
{
    if (str_contains($key, '=') || str_contains($key, "\0")) {
        Psl\invariant_violation('Invalid environment variable key provided.');
    }

    putenv($key);
}
