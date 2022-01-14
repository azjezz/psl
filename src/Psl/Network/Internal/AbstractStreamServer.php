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
     * @var resource|null $impl
     */
    private mixed $impl;

    private ?Suspension $suspension = null;
    /**
     * @var list<Suspension>
     */
    private array $queue = [];

    /**
     * @var non-empty-string
     */
    private string $watcher;

    /**
     * @param resource $impl
     */
    protected function __construct(mixed $impl)
    {
        $this->impl = $impl;
        $suspension = &$this->suspension;
        $this->watcher = Async\Scheduler::onReadable(
            $this->impl,
            /**
             * @param resource|object $resource
             */
            static function (string $_watcher, mixed $resource) use (&$suspension): void {
                /** @var resource $resource */
                $sock = @stream_socket_accept($resource, timeout: 0.0);
                if ($sock !== false) {
                    /** @var Suspension $suspension */
                    $suspension->resume($sock);

                    return;
                }

                // @codeCoverageIgnoreStart
                /** @var array{file: string, line: int, message: string, type: int} $err */
                $err = error_get_last();
                /** @var Suspension $suspension */
                $suspension->throw(new Network\Exception\RuntimeException('Failed to accept incoming connection: ' . $err['message'], $err['type']));
                // @codeCoverageIgnoreEnd
            },
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

        if (null !== $this->suspension) {
            $suspension = Async\Scheduler::getSuspension();
            $this->queue[] = $suspension;
            $suspension->suspend();
        } else {
            /** @psalm-suppress MissingThrowsDocblock */
            Async\Scheduler::enable($this->watcher);
        }

        $this->suspension = Async\Scheduler::getSuspension();

        try {
            /** @var resource $stream */
            $stream = $this->suspension->suspend();

            return new Socket($stream);
        } finally {
            $suspension = array_shift($this->queue);
            if (null !== $suspension) {
                $suspension->resume();
            } else {
                Async\Scheduler::disable($this->watcher);
                $this->suspension = null;
            }
        }
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
        if (is_resource($resource)) {
            fclose($resource);
        }

        $exception = new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        $suspensions = [$this->suspension, ...$this->queue];
        $this->suspension = null;
        $this->queue = [];
        foreach ($suspensions as $suspension) {
            $suspension?->throw($exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        return $this->impl;
    }
}
