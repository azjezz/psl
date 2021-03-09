<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;

use function dirname;

/**
 * Returns a parent directory's path.
 *
 * On Windows, both forward slash `/` and back slash `\` are used
 * as directory separator character.
 *
 * In other environments, it is the forward slash `/`.
 *
 * @param positive-int $levels The number of parent directories to go up.
 *
 * @throws Psl\Exception\InvariantViolationException If $levels is not a positive integer.
 *
 * @return string the base name of the given path.
 *
 * @pure
 */
function get_directory(string $path, int $levels = 1): string
{
    /** @psalm-suppress RedundantCondition */
    Psl\invariant($levels > 0, '$levels must be a positive integer, %d given.', $levels);

    return dirname($path, $levels);
}
