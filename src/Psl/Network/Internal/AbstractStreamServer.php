<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Generator;
use Psl\Async;
use Psl\Network;
use Psl\Network\StreamServerInterface;
use Revolt\EventLoop\Suspension;

use function error_get_last;
use function fclose;
use function is_resource;
use function stream_socket_accept;

abstract class AbstractStreamServer implements StreamServerInterface
{
    /**
     * @var closed-resource|resource|null $impl
     */
    private mixed $impl;

    /**
     * @var non-empty-string
     */
    private string $watcher;

    /**
     * @var Async\Sequence<mixed, Socket>
     */
    private Async\Sequence $sequence;

    /**
     * @var null|Suspension<resource>
     */
    private ?Suspension $suspension = null;

    /**
     * @param resource $impl
     */
    protected function __construct(mixed $impl)
    {
        $this->impl = $impl;
        $this->watcher = Async\Scheduler::onReadable($this->impl, function ($_watcher, $resource) {
            $this->suspension?->resume($resource);
        });
        $this->sequence = new Async\Sequence(
            function () {
                /** @var Suspension<resource> $suspension */
                $suspension = Async\Scheduler::getSuspension();
                $this->suspension = $suspension;

                Async\Scheduler::enable($this->watcher);

                try {
                    $sock = @stream_socket_accept($suspension->suspend(), timeout: 0.0);
                    if ($sock !== false) {
                        return new Socket($sock);
                    }

                    // @codeCoverageIgnoreStart
                    /** @var array{file: string, line: int, message: string, type: int} $err */
                    $err = error_get_last();
                    throw new Network\Exception\RuntimeException('Failed to accept incoming connection: ' . $err['message'], $err['type']);
                    // @codeCoverageIgnoreEnd
                } finally {
                    $this->suspension = null;
                    Async\Scheduler::disable($this->watcher);
                }
            }
        );

        Async\Scheduler::disable($this->watcher);
    }

    /**
     * {@inheritDoc}
     */
    public function nextConnection(): Network\StreamSocketInterface
    {
        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        return $this->sequence->waitFor(null);
    }

    /**
     * {@inheritDoc}
     */
    public function incoming(): Generator
    {
        try {
            while (true) {
                // set null as key to prevent PHP from used incremental integer keys.
                yield null => $this->nextConnection();
            }
        } catch (Network\Exception\AlreadyStoppedException) {
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalAddress(): Network\Address
    {
        if (!is_resource($this->impl)) {
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
        Async\Scheduler::disable($this->watcher);
        if (null === $this->impl) {
            return;
        }

        $resource = $this->impl;
        $this->impl = null;
        if (is_resource($resource)) {
            fclose($resource);
        }

        $this->suspension?->throw(new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.'));
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        /** @var resource */
        return $this->impl;
    }
}
