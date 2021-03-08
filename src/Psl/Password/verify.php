<?php

declare(strict_types=1);

namespace Psl\Password;

use function password_verify;

/**
 * Checks if the given hash matches the given options.
 *
 * @pure
 */
function verify(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}
