<?php

declare(strict_types=1);

namespace Psl\IRI\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base exception for invalid arguments in the IRI component.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987
 *
 * @inheritors InvalidIRIException
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
