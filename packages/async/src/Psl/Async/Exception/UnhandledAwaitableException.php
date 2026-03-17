<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

use Psl\Exception\RuntimeException;
use Throwable;

use function sprintf;

final class UnhandledAwaitableException extends RuntimeException implements ExceptionInterface
{
    public static function forThrowable(Throwable $throwable): UnhandledAwaitableException
    {
        return new self(
            sprintf(
                'Unhandled awaitable error "%s", make sure to call `Awaitable::await()` before the awaitable is destroyed, or call `Awaitable::ignore()` to ignore exceptions.',
                $throwable::class,
            ),
            (int) $throwable->getCode(),
            $throwable,
        );
    }
}
