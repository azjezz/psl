<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function realpath;

/**
 * Returns canonicalized absolute pathname.
 *
 * @return string|null The canonicalized absolute pathname on success.
 *                     The resulting path will have no symbolic link, '/./' or '/../' components.
 */
function canonicalize(string $path): ?string
{
    return realpath($path) ?: null;
}
