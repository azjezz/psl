<?php

declare(strict_types=1);

namespace Psl\Hash;

/**
 * Generate a hash value (message digest).
 *
 * @throws Exception\RuntimeException If unable to pump data into the hashing context.
 *
 * @pure
 */
function hash(string $data, Algorithm $algorithm): string
{
    return Context::forAlgorithm($algorithm)->update($data)->finalize();
}
