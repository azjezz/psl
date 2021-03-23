<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl\Str;

use const PATH_SEPARATOR;

/**
 * Joins a collection of paths appropriately for the PATH environment variable.
 *
 * @param string ...$paths
 *
 * @no-named-arguments
 */
function join_paths(string ...$paths): string
{
    return Str\join($paths, PATH_SEPARATOR);
}
