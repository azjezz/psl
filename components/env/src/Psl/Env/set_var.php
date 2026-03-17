<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;

use function putenv;
use function str_contains;

/**
 * Sets the environment variable $key to the value $value for the currently running process.
 *
 * @param non-empty-string $key
 *
 * @throws Psl\Exception\InvariantViolationException If $key contains an ASCII equals sign `=`, the NUL character `\0`,
 *                                                   or when the $value contains the NUL character.
 */
function set_var(string $key, string $value): void
{
    if (str_contains($key, '=') || str_contains($key, "\0")) {
        Psl\invariant_violation('Invalid environment variable key provided.');
    }

    if (str_contains($value, "\0")) {
        Psl\invariant_violation('Invalid environment variable value provided.');
    }

    putenv($key . '=' . $value);
}
