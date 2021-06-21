<?php

declare(strict_types=1);

namespace Psl\Asio\Exception;

final class TimeoutException extends RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = 'operation timed out.', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
