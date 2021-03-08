<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * Returns trailing name component of path.
 *
 * @return string the base name of the given path.
 *
 * @pure
 */
function get_filename(string $path): string
{
    return pathinfo($path, PATHINFO_FILENAME);
}
