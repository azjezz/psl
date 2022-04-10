<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_dir;

/**
 * Check whether $node exists and is a directory.
 *
 * @param non-empty-string $node Path, absolute or relative to the current working directory.
 *                               If it is a link, it will be resolved and checked.
 */
function is_directory(string $node): bool
{
    return is_dir($node);
}
