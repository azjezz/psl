<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function is_link as php_is_link;

/**
 * Check whether $symbolic_link is a symbolic link.
 *
 * @param string $symbolic_link Path to the file.
 *
 * If $symbolic_link is a relative filename, it will be checked relative to
 * the current working directory.
 *
 * @return bool if the $symbolic_link exists and is a symbolic link, false otherwise.
 */
function is_symbolic_link(string $symbolic_link): bool
{
    return php_is_link($symbolic_link);
}
