<?php

declare(strict_types=1);

namespace Psl\Env;

use Psl\Str;

use const PATH_SEPARATOR;

/**
 * Parses input according to platform conventions for the PATH environment variable.
 *
 * @return string[]
 */
function split_paths(string $path): array
{
    return Str\split($path, PATH_SEPARATOR);
}
