<?php

declare(strict_types=1);

namespace Psl\Env;

use function explode;

use const PATH_SEPARATOR;

/**
 * Parses input according to platform conventions for the PATH environment variable.
 *
 * @return list<string>
 *
 * @pure
 */
function split_paths(string $path): array
{
    return explode(PATH_SEPARATOR, $path);
}
