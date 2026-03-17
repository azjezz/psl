<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * Get the last component of $node, excluding extension.
 *
 * @param non-empty-string $node
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_filename(string $node): string
{
    /** @var non-empty-string */
    return pathinfo($node, PATHINFO_FILENAME);
}
