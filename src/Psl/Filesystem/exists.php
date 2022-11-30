<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function file_exists;

/**
 * Check whether $node exists.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 */
function exists(string $node): bool
{
    return file_exists($node);
}
