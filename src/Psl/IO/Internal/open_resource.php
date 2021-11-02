<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Internal;

use function error_get_last;

/**
 * @return resource
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function open_resource(string $uri, string $mode): mixed
{
    return Internal\suppress(
        /**
         * @return resource
         */
        static function () use ($uri, $mode) {
            $resource = fopen($uri, $mode);
            if ($resource === false) {
                $error = error_get_last();
                $message = $error['message'] ?? 'Unable to open resource.';
                Psl\invariant_violation($message);
            }

            return $resource;
        },
    );
}
