<?php

declare(strict_types=1);

namespace Psl\Env;

use function implode;

use const PATH_SEPARATOR;

/**
 * Joins a collection of paths appropriately for the PATH environment variable.
 *
 * @param string ...$paths
 *
 * @no-named-arguments
 *
 * @pure
 */
function join_paths(string ...$paths): string
{
    return implode(PATH_SEPARATOR, $paths);
}
