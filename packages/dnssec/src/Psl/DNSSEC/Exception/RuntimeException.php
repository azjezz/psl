<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base runtime exception for DNSSEC operations.
 *
 * @api
 *
 * @inheritors SignatureFailedException|BrokenTrustChainException|InvalidProofException|UnsignedResponseException
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    /**
     * @param string $message The error message.
     * @param null|Throwable $previous The previous exception, if any.
     */
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
