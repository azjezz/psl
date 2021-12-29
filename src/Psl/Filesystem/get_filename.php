<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * Returns trailing name component of path.
 *
 * @param non-empty-string $node
 *
 * @return non-empty-string the base name of the given path.
 *
 * @pure
 */
function get_filename(string $node): string
{
    /** @var non-empty-string */
    return pathinfo($node, PATHINFO_FILENAME);
}
