<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\Exception;
use Psl\Channel\SenderInterface;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

/**
 * @template T
 *
 * @implements SenderInterface<T>
 */
final class BoundedSender implements SenderInterface
{
    /**
     * @use ChannelSideTrait<BoundedChannelState<T>>
     */
    use ChannelSideTrait;

    private null|Suspension $suspension = null;

    /**
     * @param BoundedChannelState<T> $state
     */
    public function __construct(BoundedChannelState $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function send(mixed $message): void
    {
        if ($this->suspension) {
            $this->suspension = $suspension = EventLoop::getSuspension();
            $this->state->waitForSpace($suspension);
            $suspension->suspend();
        }

        try {
            $this->state->send($message);
        } catch (Exception\FullChannelException) {
            $this->suspension = $suspension = EventLoop::getSuspension();
            $this->state->waitForSpace($suspension);
            $suspension->suspend();

            /** @psalm-suppress MissingThrowsDocblock */
            $this->state->send($message);
        } finally {
            $this->suspension = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function trySend(mixed $message): void
    {
        $this->state->send($message);
    }
}
