<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

use Exception as RootException;
use Psl\Exception\RuntimeException;
use Psl\Str;

final class UnhandledAwaitableException extends RuntimeException implements ExceptionInterface
{
    public static function forException(RootException $exception): UnhandledAwaitableException
    {
        return new self(
            Str\format('Unhandled awaitable error "%s", make sure to call `Awaitable::await()` before the awaitable is destroyed, or call `Awaitable::ignore()` to ignore exceptions.', $exception::class),
            (int) $exception->getCode(),
            $exception
        );
    }
}
