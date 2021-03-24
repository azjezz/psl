<?php

declare(strict_types=1);

namespace Psl\Hash\Hmac;

use Psl;
use Psl\Hash;

/**
 * Generate a keyed hash value using the HMAC method.
 *
 * @throws Psl\Exception\InvariantViolationException If the given algorithm is unsupported.
 * @throws Hash\Exception\RuntimeException If unable to pump data into the hashing context.
 *
 * @pure
 */
function hash(string $data, string $algorithm, string $key): string
{
    return Hash\Context::hmac($algorithm, $key)->update($data)->finalize();
}
