<?php

declare(strict_types=1);

namespace Psl\Trait;

use function trait_exists;

/**
 * Checks if the trait with the given name exists.
 *
 * @param string $traitName
 */
function exists(string $traitName): bool
{
    return trait_exists($traitName, true);
}
