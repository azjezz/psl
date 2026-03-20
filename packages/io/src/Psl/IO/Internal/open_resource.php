<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;

use function error_get_last;
use function fopen;

/**
 * @return resource
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function open_resource(string $uri, string $mode): mixed
{
    return namespace\suppress(
        /**
         * @return resource
         */
        static function () use ($uri, $mode): mixed {
            $resource = fopen($uri, $mode);
            if (false === $resource) {
                $error = error_get_last();
                $message = $error['message'] ?? 'Unable to open resource.';
                Psl\invariant_violation($message);
            }

            return $resource;
        },
    );
}
