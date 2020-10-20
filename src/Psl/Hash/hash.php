<?php

declare(strict_types=1);

namespace Psl\Hash;

use Psl;

/**
 * Generate a hash value (message digest).
 *
 * @throws Psl\Exception\InvariantViolationException If the given algorithm is unsupported.
 *
 * @psalm-pure
 */
function hash(string $data, string $algorithm): string
{
    return Context::forAlgorithm($algorithm)->update($data)->finalize();
}
