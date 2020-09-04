<?php

declare(strict_types=1);

namespace Psl\Password;

use const PASSWORD_BCRYPT;
use function password_hash;
use Psl;
use Psl\Arr;

/**
 * Creates a password hash.
 *
 * The following algorithms are currently supported:
 *      `Psl\Password\DEFAULT_ALGORITHM`    - Use the default algorithm ( bcrypt ).
 *      `Psl\Password\BCRYPT_ALGORITHM`     - Use the blowfish algorithm to create the hash.
 *      `Psl\Password\ARGON2I_ALGORITHM`    - Use the argon2i hashing algorithm to create the hash.
 *      `Psl\Password\ARGON2ID_ALGORITHM`   - Use the argon2id hashing algorithm to create the hash.
 *
 * @psalm-param array{?cost: int}|array{?memory_cost: int, ?time_cost: int, ?threads: int} $options
 *
 * @see BCRYPT_ALGORITHM for the bcrypt ( default ) supported options.
 * @see ARGON2I_ALGORITHM for the argon2i, and argon2id supported options.
 */
function hash(string $password, string $algorithm = DEFAULT_ALGORITHM, array $options = []): string
{
    Psl\invariant(Arr\contains(algorithms(), $algorithm), 'Unsupported algorithm "%s".', $algorithm);
    if (DEFAULT_ALGORITHM === $algorithm) {
        $algorithm = PASSWORD_BCRYPT;
        Psl\invariant(!Arr\contains_key($options, 'salt'), 'The "salt" option is not supported.');
    }

    return password_hash($password, $algorithm, $options);
}
