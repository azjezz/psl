<?php

declare(strict_types=1);

namespace Psl\OS;

use const PHP_OS_FAMILY;

/**
 * Return whether PHP was built for Windows OS family.
 */
function is_windows(): bool
{
    return PHP_OS_FAMILY === 'Windows';
}
