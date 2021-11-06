<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl;
use Psl\Network;

use function fclose;

use const PHP_OS_FAMILY;

final class Server implements Network\ServerInterface
{
    /**
     * @param resource|null $impl
     */
    private function __construct(
        private mixed $impl
    ) {
    }

    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $host
     * @param positive-int|0 $port
     *
     * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(
        string $host,
        int $port = 0,
        ?ServerOptions  $options = null,
    ): self {
        $server_options = $options ?? ServerOptions::create();
        $socket_options = $server_options->socketOptions;
        $socket_context = [
            'socket' => [
                'ipv6_v6only' => true,
                'so_reuseaddr' => PHP_OS_FAMILY === 'Windows' ? $socket_options->portReuse : $socket_options->addressReuse,
                'so_reuseport' => $socket_options->portReuse,
                'so_broadcast' => $socket_options->broadcast,
                'tcp_nodelay' => $server_options->noDelay,
            ]
        ];

        $socket = Network\Internal\server_listen("tcp://{$host}:{$port}", $socket_context);

        return new self($socket);
    }

    /**
     * {@inheritDoc}
     */
    public function nextConnection(): SocketInterface
    {
        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        // @codeCoverageIgnoreStart
        try {
            /** @psalm-suppress MissingThrowsDocblock */
            return new Internal\Socket(
                Network\Internal\socket_accept($this->impl)
            );
        } catch (Network\Exception\AlreadyStoppedException $exception) {
            $this->impl = null;

            throw $exception;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalAddress(): Network\Address
    {
        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        return Network\Internal\get_sock_name($this->impl);
    }

    /**
     * {@inheritDoc}
     */
    public function stopListening(): void
    {
        if (null === $this->impl) {
            return;
        }

        $resource = $this->impl;
        $this->impl = null;
        fclose($resource);
    }

    public function __destruct()
    {
        $this->stopListening();
    }
}
