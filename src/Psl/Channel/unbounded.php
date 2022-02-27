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
    $channel = new Internal\UnboundedChannelState();

    return [
        new Internal\UnboundedReceiver($channel),
        new Internal\UnboundedSender($channel),
    ];
}
