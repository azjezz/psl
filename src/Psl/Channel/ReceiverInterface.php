<?php

declare(strict_types=1);

namespace Psl\Channel;

/**
 * @template T
 *
 * @extends ChannelInterface<T>
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
     *
     * @return T
     */
    public function receive(): mixed;

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
