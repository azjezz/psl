<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl;
use Psl\IO;
use Psl\Network;
use Psl\OS;

use function stream_context_get_options;

final class Server extends Network\Internal\AbstractStreamServer
{
    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $host
     * @param int<0, max> $port
     *
     * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(string $host, int $port = 0, ?ServerOptions $options = null): self
    {
        $server_options = $options ?? ServerOptions::create();
        $socket_options = $server_options->socketOptions;
        $socket_context = [
            'socket' => [
                'ipv6_v6only' => true,
                'so_reuseaddr' => OS\is_windows() ? $socket_options->portReuse : $socket_options->addressReuse,
                'so_reuseport' => $socket_options->portReuse,
                'so_broadcast' => $socket_options->broadcast,
                'tcp_nodelay' => $server_options->noDelay,
            ]
        ];

        if (null !== $server_options->tlsServerOptions) {
            $socket_context['ssl'] = TLS\Internal\server_context(
                $server_options->tlsServerOptions
            );
        }

        $socket = Network\Internal\server_listen("tcp://$host:$port", $socket_context);

        return new static($socket, $server_options->idleConnections);
    }

    /**
     * @inheritDoc
     *
     * @throws Psl\IO\Exception\RuntimeException
     */
    protected function wrap(mixed $stream): Network\Internal\Socket
    {
        $context = stream_context_get_options($stream);
        /** @var array|null $ssl_context */
        $ssl_context = $context['ssl'] ?? null;
        if (null !== $ssl_context) {
            try {
                TLS\Internal\establish_tls_connection($stream, $ssl_context);
            } catch (Psl\TCP\TLS\Exception\NegotiationException $e) {
                IO\Internal\close_resource($stream);

                throw $e;
            }
        }

        return new Network\Internal\Socket($stream);
    }
}
