<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_SAPI;

/**
 * Return the Server API for this build of PHP.
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_sapi(): string
{
    /** @var non-empty-string */
    return PHP_SAPI;
}
