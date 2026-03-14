<?php

declare(strict_types=1);

namespace Psl\Channel;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * @template T
 */
interface SenderInterface extends ChannelInterface
{
    /**
     * Send a message to the channel.
     *
     * If the channel is full, this method waits until there is space for a message.
     *
     * If the channel is closed, this method throws.
     *
     * @param T $message
     *
     * @throws Exception\ClosedChannelException If the channel is closed.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     */
    public function send(mixed $message, CancellationTokenInterface $cancellation = new NullCancellationToken()): void;

    /**
     * Receives a message from the channel immediately.
     *
     * If the channel is full, this method will throw an exception.
     *
     * If the channel is closed, this method throws.
     *
     * @param T $message
     *
     * @throws Exception\ClosedChannelException If the channel is closed.
     * @throws Exception\FullChannelException If the channel is full.
     */
    public function trySend(mixed $message): void;
}
