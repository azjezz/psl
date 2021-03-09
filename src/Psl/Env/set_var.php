<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl;
use Psl\Str;

use function putenv;

/**
 * Sets the environment variable $key to the value $value for the currently running process.
 *
 * @throws Psl\Exception\InvariantViolationException If $key is empty, contains an ASCII equals sign `=`,
 *                                                   the NUL character `\0`, or when the $value contains
 *                                                   the NUL character.
 */
function set_var(string $key, string $value): void
{
    Psl\invariant(
        !Str\is_empty($key) && !Str\contains($key, '=') && !Str\contains($key, "\0"),
        'Invalid environment variable key provided.'
    );

    Psl\invariant(!Str\contains($value, "\0"), 'Invalid environment variable value provided.');

    putenv(Str\format('%s=%s', $key, $value));
}
