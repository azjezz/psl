<?php

declare(strict_types=1);

namespace Psl\Punycode\Exception;

use Psl\Exception;

/**
 * Base exception for invalid arguments in the Punycode component.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492
 */
abstract class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    protected function __construct(string $message)
    {
        parent::__construct($message);
    }
}
