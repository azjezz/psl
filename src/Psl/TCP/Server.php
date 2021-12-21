<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl;
use Psl\Async;
use Psl\Network;

use function error_get_last;
use function fclose;
use function stream_socket_accept;

use const PHP_OS_FAMILY;

final class Server implements Network\StreamServerInterface
{
    /**
     * @var resource|null $impl
     */
    private mixed $impl;

    /**
     * @var null|Async\Deferred<resource>
     */
    private ?Async\Deferred $deferred = null;

    /**
     * @var non-empty-string
     */
    private string $watcher;

    /**
     * @param resource $impl
     */
    private function __construct(mixed $impl)
    {
        $this->impl = $impl;
        $deferred = &$this->deferred;
        $this->watcher = Async\Scheduler::onReadable(
            $this->impl,
            /**
             * @param resource|object $resource
             */
            static function (string $_watcher, mixed $resource) use (&$deferred): void {
                /** @var resource $resource */
                $sock = @stream_socket_accept($resource, timeout: 0.0);
                if ($sock !== false) {
                    /** @var Async\Deferred<resource>|null $deferred */
                    $deferred?->complete($sock);

                    return;
                }

                // @codeCoverageIgnoreStart
                /** @var array{file: string, line: int, message: string, type: int} $err */
                $err = error_get_last();
                /** @var Async\Deferred<resource>|null $deferred */
                $deferred?->error(new Network\Exception\RuntimeException('Failed to accept incoming connection: ' . $err['message'], $err['type']));
                // @codeCoverageIgnoreEnd
            },
        );
        Async\Scheduler::disable($this->watcher);
    }

    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $host
     * @param int<0, max> $port
     *
     * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(
        string         $host,
        int            $port = 0,
        ?ServerOptions $options = null,
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
    public function nextConnection(): Network\StreamSocketInterface
    {
        $this->deferred?->getAwaitable()->then(static fn() => null, static fn() => null)->await();

        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        /** @var Async\Deferred<resource> */
        $this->deferred = new Async\Deferred();

        /** @psalm-suppress MissingThrowsDocblock */
        Async\Scheduler::enable($this->watcher);

        try {
            /** @psalm-suppress PossiblyNullReference */
            return new Network\Internal\Socket($this->deferred->getAwaitable()->await());
        } finally {
            Async\Scheduler::disable($this->watcher);
            $this->deferred = null;
        }
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

    public function __destruct()
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        Async\Scheduler::cancel($this->watcher);

        if (null === $this->impl) {
            return;
        }

        $resource = $this->impl;
        $this->impl = null;
        fclose($resource);

        $deferred = $this->deferred;
        $this->deferred = null;
        $deferred?->error(new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.'));
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        return $this->impl;
    }
}
