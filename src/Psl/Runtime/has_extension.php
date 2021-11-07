<?php

declare(strict_types=1);

namespace Psl\Runtime;

use function extension_loaded;

/**
 * Find out whether an $extension is loaded.
 *
 * @param non-empty-string $extension
 *
 * @pure
 */
function has_extension(string $extension): bool
{
    return extension_loaded($extension);
}
