<?php

declare(strict_types=1);

namespace Psl\Channel\Exception;

use Psl\Channel;
use Psl\Exception\RuntimeException;

/**
 * This exception is thrown when attempting to send or receive a message on a closed channel.
 *
 * @see Channel\SenderInterface::send()
 * @see Channel\SenderInterface::trySend()
 * @see Channel\ReceiverInterface::receive()
 * @see Channel\ReceiverInterface::tryReceive()
 */
final class ClosedChannelException extends RuntimeException implements ExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forSending(): ClosedChannelException
    {
        return new self('Attempted to send a message to a closed channel.');
    }

    public static function forReceiving(): ClosedChannelException
    {
        return new self('Attempted to receive a message from a closed empty channel.');
    }
}
