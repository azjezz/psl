<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base runtime exception for the MIME component.
 *
 * Thrown when an operation fails at runtime due to invalid input data, I/O errors,
 * or external system failures (e.g., OpenSSL). Subclasses specialize the failure domain.
 *
 * @inheritors ParsingException|SMIMEException|CMSException|MultiPartException|EntropyException|EncodingException|DKIMException
 *
 * @see ParsingException
 * @see SMIMEException
 * @see CMSException
 * @see MultiPartException
 * @see EntropyException
 * @see EncodingException
 * @see DKIMException
 *
 * @api
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
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
