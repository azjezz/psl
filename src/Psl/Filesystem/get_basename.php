<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function basename as php_basename;

/**
 * Get the last component of $path.
 *
 * On Windows, both forward slash `/` and backslash `\` are used
 * as a directory separator character.
 *
 * In other environments, it is the forward slash `/`.
 *
 * @param non-empty-string|null $suffix If the filename ends in a suffix, it will also be cut off.
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_basename(string $path, ?string $suffix = null): string
{
    if (null === $suffix) {
        /** @var non-empty-string */
        return php_basename($path);
    }

    /** @var non-empty-string */
    return php_basename($path, $suffix);
}
