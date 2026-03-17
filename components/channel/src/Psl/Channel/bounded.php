<?php

declare(strict_types=1);

namespace Psl\Channel;

/**
 * Creates a bounded channel.
 *
 * The created channel has space to hold at most $capacity messages at a time.
 *
 * @template T
 *
 * @param positive-int $capacity
 *
 * @return array{ReceiverInterface<T>, SenderInterface<T>}
 */
function bounded(int $capacity): array
{
    $channel = new Internal\BoundedChannelState($capacity);

    return [
        new Internal\BoundedReceiver($channel),
        new Internal\BoundedSender($channel),
    ];
}
