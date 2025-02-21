<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function realpath;

/**
 * Returns canonicalized absolute pathname.
 * The resulting path will have no symbolic links, '/./' or '/../' components.
 *
 * @return non-empty-string|null
 *
 * @mago-ignore best-practices/no-boolean-literal-comparison
 */
function canonicalize(string $path): null|string
{
    $path = realpath($path);

    return false !== $path && '' !== $path ? $path : null;
}
