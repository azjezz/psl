<?php

declare(strict_types=1);

namespace Psl\Trait;

use function trait_exists;

/**
 * Checks if the trait with the given name has already been defined.
 *
 * @param string $traitName
 *
 * @pure
 */
function defined(string $traitName): bool
{
    return trait_exists($traitName, false);
}
