<?php

declare(strict_types=1);

namespace Psl\Channel;

/**
 * Creates an unbounded channel.
 *
 * The created channel can hold an unlimited number of messages.
 *
 * @return array{ReceiverInterface<T>, SenderInterface<T>}
 *
 * @api
 */
function unbounded<T>(): array
{
    $channel = new Internal\UnboundedChannelState::<T>();

    return [
        new Internal\UnboundedReceiver::<T>($channel),
        new Internal\UnboundedSender::<T>($channel),
    ];
}
