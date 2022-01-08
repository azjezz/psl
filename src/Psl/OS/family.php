<?php

declare(strict_types=1);

namespace Psl\OS;

use const PHP_OS_FAMILY;

/**
 * Return the operating system family PHP was built for.
 */
function family(): Family
{
    return Family::from(PHP_OS_FAMILY);
}
