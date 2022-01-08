<?php

declare(strict_types=1);

namespace Psl\Password;

use Psl;

use function password_needs_rehash;

/**
 * Verifies that the given hash matches the given password.
 *
 * Note that `Psl\Password\hash` returns the algorithm, cost and salt as part of the returned hash.
 * Therefore, all information that's needed to verify the hash is included in it. This allows
 * the verify function to verify the hash without needing separate storage for the salt or
 * algorithm information.
 *
 * This function is safe against timing attacks.
 *
 * @param array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int} $options
 *
 * @pure
 */
function needs_rehash(string $hash, Algorithm $algorithm = Algorithm::Default, array $options = []): bool
{
    /** @psalm-suppress ImpureMethodCall */
    return password_needs_rehash($hash, $algorithm->getBuiltinConstantValue(), $options);
}
