<?php

declare(strict_types=1);

namespace Psl\Network;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Channel;
use Revolt\EventLoop;

use function array_map;

/**
 * A listener that accepts connections from multiple inner listeners concurrently.
 *
 * Each inner listener runs its own accept loop in a separate fiber. Accepted
 * connections are funneled through a shared channel, so a single call to
 * {@see accept()} returns the next connection from any of the inner listeners.
 *
 * This is useful for servers that need to listen on multiple addresses, protocols,
 * or socket types simultaneously (e.g., TCP + Unix, or plain + TLS).
 *
 * Note: {@see getLocalAddress()} returns the address of the first inner listener.
 * Access individual listeners via the array you passed to the constructor.
 *
 * @param non-empty-list<ListenerInterface> $listeners
 */
final class CompositeListener implements ListenerInterface
{
    /**
     * @var non-empty-list<ListenerInterface>
     */
    private readonly array $listeners;

    /**
     * @var Channel\ReceiverInterface<StreamInterface>
     */
    private readonly Channel\ReceiverInterface $receiver;

    private readonly Async\SignalCancellationToken $stopToken;

    private bool $closed = false;

    /**
     * @param non-empty-list<ListenerInterface> $listeners
     */
    public function __construct(array $listeners)
    {
        $this->listeners = $listeners;
        $this->stopToken = new Async\SignalCancellationToken();

        /**
         * @var Channel\ReceiverInterface<StreamInterface> $receiver
         * @var Channel\SenderInterface<StreamInterface> $sender
         */
        [$receiver, $sender] = Channel\unbounded();
        $this->receiver = $receiver;

        $wg = new Async\WaitGroup();

        foreach ($this->listeners as $listener) {
            $wg->add();
            $this->startAcceptLoop($listener, $sender, $wg);
        }

        // When all accept loops end, close the sender so accept() throws
        EventLoop::defer(static function () use ($wg, $sender): void {
            $wg->wait();
            $sender->close();
        });
    }

    /**
     * Accept the next connection from any of the inner listeners.
     *
     * @throws Exception\AlreadyStoppedException If all listeners have been stopped.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface
    {
        try {
            return $this->receiver->receive($cancellation);
        } catch (Channel\Exception\ClosedChannelException) {
            throw new Exception\AlreadyStoppedException('All listeners have been stopped.');
        }
    }

    /**
     * Returns the local address of the first inner listener.
     */
    #[Override]
    public function getLocalAddress(): Address
    {
        return $this->listeners[0]->getLocalAddress();
    }

    /**
     * Returns the local addresses of all inner listeners.
     *
     * @return non-empty-list<Address>
     */
    public function getLocalAddresses(): array
    {
        return array_map(
            static fn(ListenerInterface $listener): Address => $listener->getLocalAddress(),
            $this->listeners,
        );
    }

    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Close all inner listeners and stop accepting connections.
     */
    #[Override]
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;
        $this->stopToken->cancel();

        foreach ($this->listeners as $listener) {
            $listener->close();
        }

        $this->receiver->close();
    }

    /**
     * @param Channel\SenderInterface<StreamInterface> $sender
     */
    private function startAcceptLoop(
        ListenerInterface $listener,
        Channel\SenderInterface $sender,
        Async\WaitGroup $wg,
    ): void {
        $token = $this->stopToken;

        EventLoop::defer(static function () use ($listener, $sender, $token, $wg): void {
            try {
                while (true) {
                    $stream = $listener->accept($token);

                    $sender->send($stream);
                }
                // @codeCoverageIgnoreStart
            } catch (CancelledException) {
                // @mago-expect lint:no-empty-catch-clause - Stop token fired, graceful shutdown
            } catch (Exception\AlreadyStoppedException) {
                // @mago-expect lint:no-empty-catch-clause - This listener was closed individually
            } catch (Channel\Exception\ClosedChannelException) {
                // @mago-expect lint:no-empty-catch-clause - Channel closed, we're shutting down
            } finally {
                // @codeCoverageIgnoreEnd
                $wg->done();
            }
        });
    }
}
