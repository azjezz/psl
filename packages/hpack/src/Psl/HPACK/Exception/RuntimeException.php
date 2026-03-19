<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

use Psl\Exception;
use Throwable;

/**
 * @inheritors DecodingException|EncodingException
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
