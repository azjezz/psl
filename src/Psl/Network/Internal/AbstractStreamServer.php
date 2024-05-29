<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Generator;
use Psl\Channel;
use Psl\Network;
use Psl\Network\StreamServerInterface;
use Revolt\EventLoop;

use function error_get_last;
use function fclose;
use function is_resource;
use function stream_socket_accept;

/**
 * @psalm-suppress UnnecessaryVarAnnotation
 */
abstract class AbstractStreamServer implements StreamServerInterface
{
    protected const DEFAULT_IDLE_CONNECTIONS = 256;

    /**
     * @var closed-resource|resource|null $impl
     */
    private mixed $impl;

    /**
     * @var string
     */
    private string $watcher;

    /**
     * @var Channel\ReceiverInterface<array{true, resource}|array{false, Network\Exception\RuntimeException}>
     */
    private Channel\ReceiverInterface $receiver;

    /**
     * @param resource $impl
     * @param int<1, max> $idleConnections
     */
    protected function __construct(mixed $impl, int $idleConnections = self::DEFAULT_IDLE_CONNECTIONS)
    {
        $this->impl = $impl;
        /**
         * @var Channel\SenderInterface<array{true, resource}|array{false, Network\Exception\RuntimeException}> $sender
         */
        [$this->receiver, $sender] = Channel\bounded($idleConnections);
        $this->watcher = EventLoop::onReadable($impl, static function ($watcher, $resource) use ($sender): void {
            try {
                $stream = @stream_socket_accept($resource, timeout: 0.0);
                if ($stream !== false) {
                    $sender->send([true, $stream]);

                    return;
                }

                // @codeCoverageIgnoreStart
                /** @var array{file: string, line: int, message: string, type: int} $err */
                $err = error_get_last();
                $sender->send([false, new Network\Exception\RuntimeException('Failed to accept incoming connection: ' . $err['message'], $err['type'])]);
                // @codeCoverageIgnoreEnd
            } catch (Channel\Exception\ClosedChannelException) {
                EventLoop::cancel($watcher);

                return;
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function nextConnection(): Network\StreamSocketInterface
    {
        return $this->wrap($this->nextConnectionImpl());
    }

    /**
     * {@inheritDoc}
     */
    public function incoming(): Generator
    {
        /** @psalm-suppress InvalidIterator */
        foreach ($this->incomingImpl() as $stream) {
            yield null => $this->wrap($stream);
        }
    }

    /**
     * @throws Network\Exception\AlreadyStoppedException
     * @throws Network\Exception\RuntimeException
     *
     * @return resource
     */
    protected function nextConnectionImpl(): mixed
    {
        try {
            [$success, $result] = $this->receiver->receive();
        } catch (Channel\Exception\ClosedChannelException) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        if ($success) {
            /** @var resource $result */
            return $result;
        }

        /** @var Network\Exception\RuntimeException $result  */
        throw $result;
    }

    /**
     * @throws Network\Exception\RuntimeException
     *
     * @return Generator<null, resource, void, null>
     */
    protected function incomingImpl(): Generator
    {
        try {
            while (true) {
                [$success, $result] = $this->receiver->receive();
                if ($success) {
                    /** @var resource $result */
                    yield null => $result;
                } else {
                    /** @var Network\Exception\RuntimeException $result  */
                    throw $result;
                }
            }
        } catch (Channel\Exception\ClosedChannelException) {
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
        EventLoop::disable($this->watcher);
        if (null === $this->impl) {
            return;
        }

        $this->receiver->close();
        $resource = $this->impl;
        $this->impl = null;
        if (is_resource($resource)) {
            fclose($resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): mixed
    {
        /** @var resource */
        return $this->impl;
    }

    /**
     * Wraps the stream into a socket object.
     *
     * @param resource $stream The stream to wrap.
     *
     * @throws Network\Exception\AlreadyStoppedException
     * @throws Network\Exception\RuntimeException
     *
     * @return Socket The wrapped socket.
     */
    protected function wrap(mixed $stream): Socket
    {
        return new Socket($stream);
    }
}
