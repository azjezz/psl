<?php

declare(strict_types=1);

namespace Psl\Hash;

use function hash_algos;

/**
 * Return a list of registered hashing algorithms.
 *
 * @return list<string>
 *
 * @pure
 */
function algorithms(): array
{
    /** @psalm-suppress ImpureFunctionCall - hash_algos is pure. */
    return hash_algos();
}
