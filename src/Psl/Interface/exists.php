<?php

declare(strict_types=1);

namespace Psl\Interface;

use function interface_exists;

/**
 * Checks if the interface with the given name exists.
 *
 * @param string $interfaceName
 */
function exists(string $interfaceName): bool
{
    return interface_exists($interfaceName, true);
}
