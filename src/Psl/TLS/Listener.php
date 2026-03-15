<?php

declare(strict_types=1);

namespace Psl\TLS;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * A TLS listener that wraps a plain listener and performs TLS handshakes on accepted connections.
 *
 * Usage:
 *
 *   $listener = new Listener(TCP\listen('0.0.0.0', 443), ServerConfig::create($certificate));
 *   while (true) {
 *       $stream = $listener->accept();
 *       // $stream is TLS-encrypted
 *   }
 */
final readonly class Listener implements ListenerInterface
{
    private Acceptor $acceptor;

    public function __construct(
        private Network\ListenerInterface $listener,
        ServerConfiguration $serverConfiguration,
    ) {
        $this->acceptor = new Acceptor($serverConfiguration);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface
    {
        $stream = $this->listener->accept($cancellation);

        return $this->acceptor->accept($stream, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->listener->getLocalAddress();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isClosed(): bool
    {
        return $this->listener->isClosed();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function close(): void
    {
        $this->listener->close();
    }
}
