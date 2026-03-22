<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Throwable;

/**
 * Thrown when the system cannot provide sufficient randomness for a MIME operation.
 *
 * This typically occurs when generating random boundaries for multipart messages
 * and the underlying CSPRNG fails.
 *
 * @api
 */
final class EntropyException extends RuntimeException
{
    /**
     * Create an exception wrapping a CSPRNG failure that prevented random byte generation.
     */
    public static function forInsufficientEntropy(Throwable $previous): self
    {
        return new self('Insufficient entropy for random generation.', $previous);
    }
}
