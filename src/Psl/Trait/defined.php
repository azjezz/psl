<?php

declare(strict_types=1);

namespace Psl\Trait;

use function trait_exists;

/**
 * Checks if the trait with the given name has already been defined.
 *
 * @param string $trait_name
 *
 * @pure
 */
function defined(string $trait_name): bool
{
    /** @var bool */
    return trait_exists($trait_name, false);
}
