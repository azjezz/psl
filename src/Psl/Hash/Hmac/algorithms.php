<?php

declare(strict_types=1);

namespace Psl\Hash\Hmac;

use function hash_hmac_algos;

/**
 * Return a list of registered hashing algorithms suitable for `Psl\Hash\Hmac\hash()`.
 *
 * @return list<string>
 *
 * @pure
 */
function algorithms(): array
{
    /** @psalm-suppress ImpureFunctionCall - hash_hmac_algos is pure. */
    return hash_hmac_algos();
}
