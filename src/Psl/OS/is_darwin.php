<?php

declare(strict_types=1);

namespace Psl\OS;

use const PHP_OS_FAMILY;

/**
 * Return whether PHP was built for Darwin OS family.
 *
 * @pure
 */
function is_darwin(): bool
{
    return PHP_OS_FAMILY === 'Darwin';
}
