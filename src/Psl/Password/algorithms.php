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
        BCRYPT_ALGORITHM,
        ARGON2I_ALGORITHM,
        ARGON2ID_ALGORITHM,
    ];
}
