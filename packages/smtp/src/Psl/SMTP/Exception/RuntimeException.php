<?php

declare(strict_types=1);

namespace Psl\SMTP\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base runtime exception for SMTP operations.
 *
 * @inheritors ConnectionException|AuthenticationException|TransmissionException|TimeoutException|ProtocolException|PossibleAttackException
 *
 * @api
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
