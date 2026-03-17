<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_VERSION;

/**
 * Return the current PHP version as a string in "major.minor.release[extra]" notation.
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_version(): string
{
    return PHP_VERSION;
}
