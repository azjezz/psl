<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function file_exists;

/**
 * Check whether $node exists.
 *
 * @param string $node Path to the file.
 *
 * If $node is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool true if $node exists, false otherwise.
 *
 * @psalm-assert-if-true non-empty-string $node
 */
function exists(string $node): bool
{
    return file_exists($node);
}
