<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function pathinfo;

/**
 * Returns the $filename extension.
 *
 * @return string|null the $filename extensions, or null if none.
 *
 * @pure
 */
function get_extension(string $filename): ?string
{
    return pathinfo($filename)['extension'] ?? null;
}
