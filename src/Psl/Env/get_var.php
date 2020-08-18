<?php

declare(strict_types=1);

namespace Psl\Env;

use function getenv;
use Psl;
use Psl\Str;

/**
 * Fetches the environment variable $key from the current process.
 *
 * @throws Psl\Exception\InvariantViolationException If $key is empty, or contains an ASCII equals sign `=` or the NUL character `\0`.
 */
function get_var(string $key): ?string
{
    Psl\invariant(!Str\is_empty($key) && !Str\contains($key, '=') && !Str\contains($key, "\0"), 'Invalid environment variable key provided.');

    /** @var false|string $value */
    $value = getenv($key);

    return false === $value ? null : $value;
}
