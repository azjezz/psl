<?php

declare(strict_types=1);

namespace Psl\TCP\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Channel;
use Psl\Network;
use Psl\TCP;
use Revolt\EventLoop;

use function error_clear_last;
use function error_get_last;
use function fclose;
use function is_resource;
use function str_contains;
use function stream_socket_accept;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Listener implements TCP\ListenerInterface
{
    private const int DEFAULT_IDLE_CONNECTIONS = 256;

    /**
     * @var closed-resource|resource|null $impl
     */
    private mixed $impl;

    private string $watcher;

    /**
     * @var Channel\ReceiverInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}>
     */
    private Channel\ReceiverInterface $receiver;

    private readonly Network\Address $localAddress;

    /**
     * @param resource $impl
     * @param int<1, max> $idleConnections
     */
    public function __construct(mixed $impl, int $idleConnections = self::DEFAULT_IDLE_CONNECTIONS)
    {
        $this->impl = $impl;
        $this->localAddress = Network\Internal\get_sock_name($impl);

        /**
         * @var Channel\ReceiverInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}> $receiver
         * @var Channel\SenderInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}> $sender
         */
        [$receiver, $sender] = Channel\bounded($idleConnections);

        $this->receiver = $receiver;
        $this->watcher = EventLoop::onReadable($impl, static function (string $watcher, mixed $resource) use (
            $sender,
        ): void {
            try {
                while (true) {
                    error_clear_last();
                    $sock = @stream_socket_accept($resource, timeout: 0.0);
                    if ($sock !== false) {
                        $sender->send([true, new Stream($sock)]);
                        continue;
                    }

                    // @codeCoverageIgnoreStart
                    $err = error_get_last();
                    if ($err !== null && !str_contains($err['message'], 'Accept failed')) {
                        // OS error (e.g., EMFILE, ENFILE, ENOBUFS)
                        $sender->send([
                            false,
                            new Network\Exception\RuntimeException(
                                'Failed to accept incoming connection: ' . $err['message'],
                                $err['type'],
                            ),
                        ]);

                        return;
                    }

                    // No more pending connections (EAGAIN / timeout with no backlog).
                    break;
                    // @codeCoverageIgnoreEnd
                }
            } catch (Channel\Exception\ClosedChannelException) {
                EventLoop::cancel($watcher);
                return;
            }
        });
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): TCP\StreamInterface
    {
        try {
            [$success, $result] = $this->receiver->receive($cancellation);
        } catch (Channel\Exception\ClosedChannelException) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        if ($success) {
            /** @var Stream $result */
            return $result;
        }

        /** @var Network\Exception\RuntimeException $result */
        throw $result;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->localAddress;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isClosed(): bool
    {
        return null === $this->impl;
    }

    /**
     * @inheritDoc
     */
    #[Override]
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

    public function __destruct()
    {
        $this->close();
    }
}
