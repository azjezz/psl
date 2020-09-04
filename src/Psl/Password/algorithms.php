<?php

declare(strict_types=1);

namespace Psl\Password;

use function defined;

/**
 * Return a complete list of all registered password hashing algorithms.
 *
 * @psalm-return list<string>
 */
function algorithms(): array
{
    $algorithms = [BCRYPT_ALGORITHM];

    if (defined('Psl\Password\ARGON2I_ALGORITHM')) {
        $algorithms[] = ARGON2I_ALGORITHM;
    }

    if (defined('Psl\Password\ARGON2ID_ALGORITHM')) {
        $algorithms[] = ARGON2ID_ALGORITHM;
    }

    return $algorithms;
}
