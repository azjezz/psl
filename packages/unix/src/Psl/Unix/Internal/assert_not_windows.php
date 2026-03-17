<?php

declare(strict_types=1);

namespace Psl\Unix\Internal;

use Psl\Network;

use const PHP_OS_FAMILY;

/**
 * Assert that the current platform is not Windows.
 *
 * @throws Network\Exception\RuntimeException If the current platform is Windows.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function assert_not_windows(): void
{
    if (PHP_OS_FAMILY === 'Windows') {
        throw new Network\Exception\RuntimeException('Unix sockets are not supported on Windows platform.');
    }
}
