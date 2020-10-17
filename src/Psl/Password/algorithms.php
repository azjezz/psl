<?php

declare(strict_types=1);

namespace Psl\Password;

/**
 * Return a complete list of all registered password hashing algorithms.
 *
 * @psalm-return list<string>
 *
 * @psalm-pure
 */
function algorithms(): array
{
    return [
        (string) BCRYPT_ALGORITHM,
        (string) ARGON2I_ALGORITHM,
        (string) ARGON2ID_ALGORITHM,
    ];
}
