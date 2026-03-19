<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

use Psl\Exception;
use Throwable;

/**
 * @inheritors HeaderListSizeException|IntegerOverflowException
 */
class OverflowException extends Exception\OverflowException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
