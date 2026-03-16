<?php

declare(strict_types=1);

namespace Psl\Channel\Exception;

use OutOfBoundsException;
use Psl\Channel;

use function sprintf;

/**
 * This exception is throw when calling {@see Channel\SenderInterface::trySend()} on a full channel.
 */
final class FullChannelException extends OutOfBoundsException implements ExceptionInterface
{
    public static function ofCapacity(int $capacity): FullChannelException
    {
        return new self(sprintf('Channel has reached its full capacity of %d.', $capacity));
    }
}
