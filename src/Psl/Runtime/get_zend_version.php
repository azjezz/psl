<?php

declare(strict_types=1);

namespace Psl\Runtime;

use function zend_version;

/**
 * Return the version of the current Zend engine.
 *
 * @return non-empty-string
 *
 * @pure
 */
function get_zend_version(): string
{
    /**
     * @psalm-suppress ImpureFunctionCall
     *
     * @var non-empty-string
     */
    return zend_version();
}
