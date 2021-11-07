<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_VERSION_ID;

/**
 * Return the current PHP version as an integer, useful for version comparisons (e.g., int(80100) from version "8.1.0-RC5").
 *
 * @return positive-int
 *
 * @pure
 */
function get_version_id(): int
{
    /** @var positive-int */
    return PHP_VERSION_ID;
}
