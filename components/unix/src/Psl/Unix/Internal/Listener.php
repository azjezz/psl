<?php

declare(strict_types=1);

namespace Psl\Unix\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Channel;
use Psl\Network;
use Psl\Unix;
use Revolt\EventLoop;

use function error_get_last;
use function fclose;
use function is_resource;
use function stream_socket_accept;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Listener implements Unix\ListenerInterface
{
    private const int DEFAULT_IDLE_CONNECTIONS = 256;

    /**
     * @var closed-resource|resource|object|null $impl
     */
    private mixed $impl;

    private string $watcher;

    /**
     * @var Channel\ReceiverInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}>
     */
    private Channel\ReceiverInterface $receiver;

    private readonly Network\Address $localAddress;

    /**
     * @param resource|object $impl
     * @param int<1, max> $idleConnections
     */
    public function __construct(mixed $impl, int $idleConnections = self::DEFAULT_IDLE_CONNECTIONS)
    {
        $this->impl = $impl;
        // @mago-expect analysis:possibly-invalid-argument - revolt signature is wrong.
        $this->localAddress = Network\Internal\get_sock_name($impl);

        /**
         * @var Channel\ReceiverInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}> $receiver
         * @var Channel\SenderInterface<array{true, Stream}|array{false, Network\Exception\RuntimeException}> $sender
         */
        [$receiver, $sender] = Channel\bounded($idleConnections);

        $this->receiver = $receiver;
        // @mago-expect analysis:possibly-invalid-argument - revolt signature is wrong.
        $this->watcher = EventLoop::onReadable(
            $impl,
            /**
             * @param resource $resource
             */
            static function (string $watcher, mixed $resource) use ($sender): void {
                try {
                    $sock = @stream_socket_accept($resource, timeout: 0.0);
                    if (false !== $sock) {
                        $sender->send([true, new Stream($sock)]);

                        return;
                    }

                    // @codeCoverageIgnoreStart
                    /** @var array{file: string, line: int, message: string, type: int} $err */
                    $err = error_get_last();
                    $sender->send([
                        false,
                        new Network\Exception\RuntimeException(
                            'Failed to accept incoming connection: ' . $err['message'],
                            $err['type'],
                        ),
                    ]);
                    // @codeCoverageIgnoreEnd
                } catch (Channel\Exception\ClosedChannelException) {
                    EventLoop::cancel($watcher);

                    return;
                }
            },
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): Unix\StreamInterface
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
