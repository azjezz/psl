<?php

declare(strict_types=1);

namespace Psl\Trait;

use function trait_exists;

/**
 * Checks if the trait with the given name exists.
 *
 * @param string $trait_name
 */
function exists(string $trait_name): bool
{
    /** @var bool */
    return trait_exists($trait_name, true);
}
