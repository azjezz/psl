<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl;
use Psl\Internal;

use function stream_context_create;
use function stream_socket_server;

use const STREAM_SERVER_BIND;
use const STREAM_SERVER_LISTEN;

/**
 * @param non-empty-string $uri
 * @param array<string, mixed> $context
 *
 * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
 *
 * @return resource
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function server_listen(string $uri, array $context = []): mixed
{
    return Internal\suppress(
        /**
         * @return resource
         */
        static function () use ($uri, $context): mixed {
            $context = stream_context_create($context);
            // Error reporting suppressed since stream_socket_server() emits an E_WARNING on failure (checked below).
            $server = @stream_socket_server($uri, $errno, $_, flags: STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, context: $context);
            if (!$server || $errno) {
                throw new Psl\Network\Exception\RuntimeException('Failed to listen to on given address (' . $uri . ').', $errno);
            }

            return $server;
        },
    );
}
