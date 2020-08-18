<?php

declare(strict_types=1);

namespace Psl\Env;

use const PATH_SEPARATOR;
use Psl\Str;

/**
 * Joins a collection of paths appropriately for the PATH environment variable.
 *
 * @param string ...$paths
 */
function join_paths(string ...$paths): string
{
    return Str\join($paths, PATH_SEPARATOR);
}
