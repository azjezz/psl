<?php

declare(strict_types=1);

namespace Psl\Password;

use Psl;
use Psl\Iter;

use function password_hash;

use const PASSWORD_BCRYPT;

/**
 * Creates a password hash.
 *
 * The following algorithms are currently supported:
 *      `Psl\Password\DEFAULT_ALGORITHM`    - Use the default algorithm ( bcrypt ).
 *      `Psl\Password\BCRYPT_ALGORITHM`     - Use the blowfish algorithm to create the hash.
 *      `Psl\Password\ARGON2I_ALGORITHM`    - Use the argon2i hashing algorithm to create the hash.
 *      `Psl\Password\ARGON2ID_ALGORITHM`   - Use the argon2id hashing algorithm to create the hash.
 *
 * @param array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int} $options
 *
 * @see BCRYPT_ALGORITHM for the bcrypt ( default ) supported options.
 * @see ARGON2I_ALGORITHM for the argon2i, and argon2id supported options.
 *
 * @throws Psl\Exception\InvariantViolationException The given algorithm is unsupported.
 *
 * @pure
 */
function hash(string $password, string $algorithm = DEFAULT_ALGORITHM, array $options = []): string
{
    /** @psalm-suppress ImpureFunctionCall */
    Psl\invariant(Iter\contains(algorithms(), $algorithm), 'Unsupported algorithm "%s".', $algorithm);
    if (DEFAULT_ALGORITHM === $algorithm) {
        $algorithm = PASSWORD_BCRYPT;
        /** @psalm-suppress ImpureFunctionCall */
        Psl\invariant(!Iter\contains_key($options, 'salt'), 'The "salt" option is not supported.');
    }

    /** @var false|string $hash */
    $hash = password_hash($password, $algorithm, $options);
    Psl\invariant(false !== $hash, 'Failed to compute the hash for the given password.');

    return $hash;
}
