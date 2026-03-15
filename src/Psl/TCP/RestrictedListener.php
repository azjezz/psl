<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\CIDR;
use Psl\IP;
use Psl\Network;

/**
 * A TCP listener that restricts incoming connections to a set of allowed IP addresses and CIDR blocks.
 *
 * Wraps another {@see ListenerInterface} and only accepts connections from peers whose
 * address matches the configured allow list. Rejected connections are closed silently.
 *
 * Usage:
 *
 *   $listener = new RestrictedListener(TCP\listen('0.0.0.0', 8080), [
 *       new CIDR\Block('10.0.0.0/8'),
 *       IP\Address::parse('127.0.0.1'),
 *   ]);
 */
final readonly class RestrictedListener implements ListenerInterface
{
    /**
     * @var list<IP\Address|CIDR\Block>
     */
    private array $allowedClients;

    /**
     * @param list<IP\Address|CIDR\Block> $allowedClients
     */
    public function __construct(
        private ListenerInterface $listener,
        array $allowedClients,
    ) {
        $this->allowedClients = $allowedClients;
    }

    /**
     * Accept the next allowed connection.
     *
     * Connections from disallowed peers are closed silently and the listener
     * continues waiting for the next connection.
     *
     * @throws Network\Exception\RuntimeException If failed to accept incoming connection.
     * @throws Network\Exception\AlreadyStoppedException If the listener has already been closed.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface
    {
        while (true) {
            $stream = $this->listener->accept($cancellation);
            if ($this->isAllowed($stream)) {
                return $stream;
            }

            $stream->close();
        }
    }

    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->listener->getLocalAddress();
    }

    #[Override]
    public function isClosed(): bool
    {
        return $this->listener->isClosed();
    }

    #[Override]
    public function close(): void
    {
        $this->listener->close();
    }

    private function isAllowed(Network\StreamInterface $stream): bool
    {
        $peerAddress = $stream->getPeerAddress();
        $peerIp = IP\Address::parse($peerAddress->host);
        foreach ($this->allowedClients as $allowed) {
            if ($allowed instanceof CIDR\Block && $allowed->contains($peerIp)) {
                return true;
            }

            if ($allowed instanceof IP\Address && $allowed->equals($peerIp)) {
                return true;
            }
        }

        return false;
    }
}
