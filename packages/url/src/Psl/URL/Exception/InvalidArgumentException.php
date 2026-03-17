<?php

declare(strict_types=1);

namespace Psl\URL\Exception;

use Psl\Exception;
use Throwable;

/**
 * Exception thrown when an invalid argument is provided to the URL component.
 *
 * @inheritors InvalidURLException
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
