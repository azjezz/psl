<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Async\OptionalIncrementalTimeout;
use Psl\DateTime\Duration;
use Psl\Network;
use Psl\Network\Exception;

/**
 * Connect to a socket.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(string $host, int $port = 0, ?ClientOptions $options = null, ?Duration $timeout = null): Network\StreamSocketInterface
{
    $optional_timeout = new OptionalIncrementalTimeout($timeout, static function (): void {
        throw new Exception\TimeoutException('Connection to socket timed out.');
    });

    $options ??= ClientOptions::create();

    $context = [
        'socket' => [
            'tcp_nodelay' => $options->noDelay,
        ],
    ];

    if (null !== $options->bindTo) {
        $context['socket']['bindto'] = $options->bindTo[0] . ':' . ((string) ($options->bindTo[1] ?? 0));
    }

    $socket = Network\Internal\socket_connect("tcp://$host:$port", $context, $optional_timeout->getRemaining());
    $tls_options = $options->tlsClientOptions;

    if (null !== $tls_options) {
        $context = TLS\Internal\client_context($host, $tls_options);

        TLS\Internal\establish_tls_connection($socket, $context, $optional_timeout->getRemaining());
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return new Network\Internal\Socket($socket);
}
