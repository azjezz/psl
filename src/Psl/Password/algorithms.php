<?php

declare(strict_types=1);

namespace Psl\Password;

/**
 * Return a complete list of all registered password hashing algorithms.
 *
 * @return list<string>
 *
 * @pure
 */
function algorithms(): array
{
    /** @var list<string> */
    return [
        BCRYPT_ALGORITHM,
        ARGON2I_ALGORITHM,
        ARGON2ID_ALGORITHM,
    ];
}
