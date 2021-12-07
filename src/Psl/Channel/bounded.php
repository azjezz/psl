<?php

declare(strict_types=1);

namespace Psl\Channel;

use Psl;

/**
 * Creates a bounded channel.
 *
 * The created channel has space to hold at most $capacity messages at a time.
 *
 * @template T
 *
 * @param positive-int $capacity
 *
 * @throws Psl\Exception\InvariantViolationException If $capacity is not a positive integer.
 *
 * @return array{0: ReceiverInterface<T>, 1: SenderInterface<T>}
 */
function bounded(int $capacity): array
{
    $channel = new Internal\ChannelState($capacity);

    return [
        new Internal\Receiver($channel),
        new Internal\Sender($channel),
    ];
}
