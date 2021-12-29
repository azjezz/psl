<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function dirname;

/**
 * Returns a parent directory's path.
 *
 * On Windows, both forward slash `/` and back slash `\` are used
 * as directory separator character.
 *
 * In other environments, it is the forward slash `/`.
 *
 * @param non-empty-string $node
 * @param positive-int $levels The number of parent directories to go up.
 *
 * @return non-empty-string the base name of the given path.
 *
 * @pure
 */
function get_directory(string $node, int $levels = 1): string
{
    /** @var non-empty-string */
    return dirname($node, $levels);
}
