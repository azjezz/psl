<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function dirname;

/**
 * Get a parent directory path.
 *
 * On Windows, both forward slash `/` and backslash `\` are used
 * as a directory separator character.
 *
 * In other environments, it is the forward slash `/`.
 *
 * @param non-empty-string $node
 * @param positive-int $levels The number of parent directories to go up.
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_directory(string $node, int $levels = 1): string
{
    /** @var non-empty-string */
    return dirname($node, $levels);
}
