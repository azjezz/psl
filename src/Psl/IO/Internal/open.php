<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\Internal;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $uri, string $mode): ResourceHandle
{
    $resource = open_resource($uri, $mode);

    /** @psalm-suppress MissingThrowsDocblock */
    return new ResourceHandle($resource, false, false, false);
}
