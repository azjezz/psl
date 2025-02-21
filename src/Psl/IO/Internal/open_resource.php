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
 *
 * @mago-ignore best-practices/no-boolean-literal-comparison
 */
function open_resource(string $uri, string $mode): mixed
{
    return Internal\suppress(
        /**
         * @return resource
         */
        static function () use ($uri, $mode): mixed {
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
