<?php

declare(strict_types=1);

namespace Psl\Channel;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * @template T
 */
interface ReceiverInterface extends ChannelInterface
{
    /**
     * Receives a message from the channel.
     *
     * If the channel is empty, this method waits until there is a message.
     *
     * If the channel is closed, this method receives a message or throws if there are no more messages.
     *
     * @throws Exception\ClosedChannelException If the channel is closed, and there's no more messages to receive.
     * @throws CancelledException If the cancellation token is cancelled while waiting.
     *
     * @return T
     */
    public function receive(CancellationTokenInterface $cancellation = new NullCancellationToken()): mixed;

    /**
     * Receives a message from the channel immediately.
     *
     * If the channel is empty, this method will throw an exception.
     *
     * If the channel is closed, this method receives a message or throws if there are no more messages.
     *
     * @throws Exception\ClosedChannelException If the channel is closed, and there's no more messages to receive.
     * @throws Exception\EmptyChannelException If the channel is empty.
     *
     * @return T
     */
    public function tryReceive(): mixed;
}
