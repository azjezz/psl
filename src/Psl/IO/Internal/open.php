<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Internal;

use function error_get_last;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $uri, string $mode): ResourceHandle
{
    return Internal\suppress(static function () use ($uri, $mode) {
        $resource = fopen($uri, $mode);
        if ($resource === false) {
            $error = error_get_last();
            $message = $error['message'] ?? 'Unable to open resource.';
            Psl\invariant_violation($message);
        }

        return new ResourceHandle($resource);
    });
}
