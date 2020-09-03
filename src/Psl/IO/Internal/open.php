<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Internal;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $uri, string $mode): ResourceHandle
{
    return Internal\suppress(static function () use ($uri, $mode) {
        $resource = fopen($uri, $mode);

        Psl\invariant($resource !== false, 'Unable to open resource.');

        return new ResourceHandle($resource);
    });
}
