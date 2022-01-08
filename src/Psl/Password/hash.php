<?php

declare(strict_types=1);

namespace Psl\Password;

use function password_hash;

/**
 * Creates a password hash.
 *
 * @param array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int} $options
 *
 * @pure
 */
function hash(string $password, Algorithm $algorithm = Algorithm::Default, array $options = []): string
{
    /** @psalm-suppress ImpureMethodCall */
    return password_hash($password, $algorithm->getBuiltinConstantValue(), $options);
}
