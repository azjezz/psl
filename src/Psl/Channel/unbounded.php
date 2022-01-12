<?php

declare(strict_types=1);

namespace Psl\Channel;

/**
 * Creates an unbounded channel.
 *
 * The created channel can hold an unlimited number of messages.
 *
 * @template T
 *
 * @return array{ReceiverInterface<T>, SenderInterface<T>}
 */
function unbounded(): array
{
    /**
     * @psalm-suppress MissingThrowsDocblock - $capacity is null.
     */
    $channel = new Internal\ChannelState();

    return [
        new Internal\Receiver($channel),
        new Internal\Sender($channel),
    ];
}
