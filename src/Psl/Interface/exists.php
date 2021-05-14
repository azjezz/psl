<?php

declare(strict_types=1);

namespace Psl\Interface;

use function interface_exists;

/**
 * Checks if the interface with the given name exists.
 *
 * @param string $interface_name
 */
function exists(string $interface_name): bool
{
    /** @var bool */
    return interface_exists($interface_name, true);
}
