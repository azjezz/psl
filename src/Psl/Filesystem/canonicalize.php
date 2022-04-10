<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function realpath;

/**
 * Returns canonicalized absolute pathname.
 * The resulting path will have no symbolic links, '/./' or '/../' components.
 *
 * @return non-empty-string|null
 */
function canonicalize(string $path): ?string
{
    return realpath($path) ?: null;
}
