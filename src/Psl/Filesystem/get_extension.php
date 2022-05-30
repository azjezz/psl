<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function pathinfo;

/**
 * Get the $node extension.
 *
 * @param non-empty-string $node
 *
 * @return non-empty-string|null The $node extensions, or null if none.
 *
 * @pure
 */
function get_extension(string $node): ?string
{
    $extension = pathinfo($node)['extension'] ?? null;

    return $extension !== '' ? $extension : null;
}
