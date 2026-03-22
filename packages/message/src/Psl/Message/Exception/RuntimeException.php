<?php

declare(strict_types=1);

namespace Psl\Message\Exception;

use Psl\Exception;
use Throwable;

/**
 * Thrown when a runtime error occurs in the Psl\Message component.
 *
 * @see ParsingException for the subclass used when parsing fails.
 *
 * @inheritors ParsingException
 *
 * @api
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for when no recipients could be derived from the message headers.
     *
     * @see Envelope::fromMessage()
     */
    public static function forNoRecipients(): self
    {
        return new self('Cannot derive envelope: message has no To, Cc, or Bcc recipients.');
    }
}
