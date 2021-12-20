<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function getenv;
use function str_contains;

/**
 * Fetches the environment variable $key from the current process.
 *
 * @param non-empty-string $key
 *
 * @throws Psl\Exception\InvariantViolationException If $key contains an ASCII equals sign `=`, or the NUL character `\0`.
 */
function get_var(string $key): ?string
{
    if (str_contains($key, '=') || str_contains($key, "\0")) {
        Psl\invariant_violation('Invalid environment variable key provided.');
    }

    $value = getenv($key);

    return false === $value ? null : $value;
}
