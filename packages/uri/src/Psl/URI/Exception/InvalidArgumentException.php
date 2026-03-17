<?php

declare(strict_types=1);

namespace Psl\URI\Exception;

use Psl\Exception;
use Throwable;

/**
 * @inheritors InvalidURIException|InvalidTemplateException
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
