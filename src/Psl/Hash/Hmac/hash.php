<?php

declare(strict_types=1);

namespace Psl\Hash\Hmac;

use Psl\Hash;

/**
 * Generate a keyed hash value using the HMAC method.
 *
 * @param non-empty-string $key
 *
 * @throws Hash\Exception\RuntimeException If unable to pump data into the hashing context.
 *
 * @pure
 */
function hash(string $data, Algorithm $algorithm, string $key): string
{
    return Hash\Context::hmac($algorithm, $key)->update($data)->finalize();
}
