<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base invalid-argument exception for the MIME component.
 *
 * Thrown when a caller supplies a value that violates domain constraints
 * (e.g., an illegal media type string or out-of-range quality weight).
 *
 * @inheritors InvalidMediaTypeComponentException
 *
 * @see InvalidMediaTypeComponentException
 *
 * @api
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    final protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create a new instance with the given error message.
     *
     * @internal
     */
    public static function create(string $message): static
    {
        return new static($message);
    }
}
