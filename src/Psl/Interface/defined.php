<?php

declare(strict_types=1);

namespace Psl\Interface;

use function interface_exists;

/**
 * Checks if the interface with the given name has already been defined.
 *
 * @param string $interfaceName
 *
 * @pure
 */
function defined(string $interfaceName): bool
{
    return interface_exists($interfaceName, false);
}
