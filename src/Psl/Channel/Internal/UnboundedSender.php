<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\SenderInterface;

/**
 * @template T
 *
 * @implements SenderInterface<T>
 */
final class UnboundedSender implements SenderInterface
{
    /**
     * @use ChannelSideTrait<UnboundedChannelState<T>>
     */
    use ChannelSideTrait;

    /**
     * @param UnboundedChannelState<T> $state
     */
    public function __construct(UnboundedChannelState $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function send(mixed $message): void
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $this->state->send($message);
    }

    /**
     * {@inheritDoc}
     */
    public function trySend(mixed $message): void
    {
        $this->state->send($message);
    }
}
