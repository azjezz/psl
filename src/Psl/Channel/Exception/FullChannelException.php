<?php

declare(strict_types=1);

namespace Psl\Channel\Exception;

use OutOfBoundsException;
use Psl\Channel;
use Psl\Str;

/**
 * This exception is throw when calling {@see Channel\SenderInterface::trySend()} on a full channel.
 */
final class FullChannelException extends OutOfBoundsException implements ExceptionInterface
{
    public static function ofCapacity(int $capacity): FullChannelException
    {
        return new self(Str\format('Channel has reached its full capacity of %d.', $capacity));
    }
}
