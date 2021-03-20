<?php

declare(strict_types=1);

namespace Psl\Asio\Exception;

use Psl\Exception;

final class TimeoutException extends Exception\RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = 'operation timed out.', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
