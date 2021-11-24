<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

final class TimeoutException extends RuntimeException
{
    public function __construct(string $message = 'operation timed out.')
    {
        parent::__construct($message);
    }
}
