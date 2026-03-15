<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\Network;

/**
 * A test listener that ignores close() and only responds to cancellation.
 *
 * When accept() is called, it suspends until the cancellation token fires.
 * Calling close() sets a flag but does NOT interrupt accept().
 */
final class SlowClosingListener implements Network\ListenerInterface
{
    private bool $closed = false;

    private readonly Network\Address $address;

    public function __construct()
    {
        $this->address = Network\Address::tcp('127.0.0.1', 0);
    }

    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): Network\StreamInterface
    {
        $cancellation->throwIfCancelled();

        Async\sleep(Duration::seconds(30), $cancellation);

        throw new Network\Exception\AlreadyStoppedException('Listener stopped.');
    }

    #[Override]
    public function getLocalAddress(): Network\Address
    {
        return $this->address;
    }

    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    #[Override]
    public function close(): void
    {
        $this->closed = true;
    }
}
