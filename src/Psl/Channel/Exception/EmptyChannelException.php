<?php

declare(strict_types=1);

namespace Psl\Channel\Exception;

use OutOfBoundsException;
use Psl\Channel;

/**
 * This exception is throw when calling {@see Channel\ReceiverInterface::tryReceive()} on an empty channel.
 */
final class EmptyChannelException extends OutOfBoundsException implements ExceptionInterface
{
    public static function create(): EmptyChannelException
    {
        return new self('Attempted to receiver from an empty channel.');
    }
}
