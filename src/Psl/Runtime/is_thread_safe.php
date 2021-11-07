<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_ZTS;

/**
 * Return true if PHP was built with ZTS enabled.
 *
 * @pure
 */
function is_thread_safe(): bool
{
    return 1 === PHP_ZTS;
}
