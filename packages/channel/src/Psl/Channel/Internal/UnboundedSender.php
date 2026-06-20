<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Channel\SenderInterface;

/**
 * @internal
 */
final class UnboundedSender<T> implements SenderInterface<T>
{
    use ChannelSideTrait<UnboundedChannelState<T>>;

    public function __construct(UnboundedChannelState<T> $state)
    {
        $this->state = $state;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function send(mixed $message, CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        $this->state->send($message);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function trySend(mixed $message): void
    {
        $this->state->send($message);
    }
}
