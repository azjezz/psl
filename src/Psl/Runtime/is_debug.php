<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_DEBUG;

/**
 * Return true if PHP was built with debugging enabled.
 *
 * @pure
 */
function is_debug(): bool
{
    return 1 === PHP_DEBUG;
}
