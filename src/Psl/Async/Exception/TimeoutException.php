<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

final class TimeoutException extends RuntimeException
{
    public function __construct(string $message = 'operation timed out.', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
