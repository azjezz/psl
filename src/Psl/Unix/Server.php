<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl;
use Psl\Network;
use Revolt\EventLoop;

use function error_get_last;
use function fclose;
use function stream_socket_accept;

final class Server implements Network\ServerInterface
{
    /**
     * @var resource|null $impl
     */
    private mixed $impl;
    private ?EventLoop\Suspension $suspension = null;
    private string $watcher;

    /**
     * @param resource $impl
     */
    private function __construct(mixed $impl)
    {
        $this->impl = $impl;
        $suspension = &$this->suspension;
        $this->watcher = EventLoop::onReadable(
            $this->impl,
            /**
             * @param resource|object $resource
             */
            static function (string $_watcher, mixed $resource) use (&$suspension): void {
                /**
                 * @var resource $resource
                 */
                $sock = @stream_socket_accept($resource, timeout: 0.0);
                /** @var \Revolt\EventLoop\Suspension|null $tmp */
                $tmp = $suspension;
                $suspension = null;
                if ($sock !== false) {
                    $tmp?->resume($sock);

                    return;
                }

                // @codeCoverageIgnoreStart
                /** @var array{file: string, line: int, message: string, type: int} $err */
                $err = error_get_last();
                $tmp?->throw(new Network\Exception\RuntimeException('Failed to accept incoming connection: ' . $err['message'], $err['type']));
                // @codeCoverageIgnoreEnd
            },
        );
        EventLoop::disable($this->watcher);
    }

    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $file
     *
     * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(string $file): self
    {
        $socket = Network\Internal\server_listen("unix://{$file}");

        return new self($socket);
    }

    /**
     * {@inheritDoc}
     */
    public function nextConnection(): SocketInterface
    {
        if (null !== $this->suspension) {
            throw new Network\Exception\RuntimeException('Pending operation.');
        }

        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        $this->suspension = $suspension = EventLoop::createSuspension();
        /** @psalm-suppress MissingThrowsDocblock */
        EventLoop::enable($this->watcher);

        try {
            /** @var resource $socket */
            $socket = $suspension->suspend();
        } finally {
            EventLoop::disable($this->watcher);
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return new Internal\Socket($socket);
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

        $suspension = null;
        if (null !== $this->watcher) {
            EventLoop::cancel($this->watcher);
            $suspension = $this->suspension;
            $this->suspension = null;
        }

        $resource = $this->impl;
        $this->impl = null;
        /** @psalm-suppress PossiblyNullArgument */
        fclose($resource);

        $suspension?->throw(new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.'));
    }

    public function __destruct()
    {
        $this->stopListening();
    }
}
