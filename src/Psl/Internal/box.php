<?php

declare(strict_types=1);

namespace Psl\Internal;

use Closure;
use Psl\Str;

use function restore_error_handler;
use function set_error_handler;

/**
 * @template T
 *
 * @param (Closure(): T) $fun
 *
 * @return array{0: T, 1: ?string}
 *
 * @internal
 */
function box(Closure $fun): array
{
    $lastMessage = null;
    set_error_handler(static function (int $_, string $message) use (&$lastMessage): void {
        $lastMessage = $message;
    });

    if (null !== $lastMessage && Str\contains($lastMessage, '): ')) {
        $lastMessage = Str\after(
            Str\lowercase($lastMessage),
            // how i feel toward PHP error handling:
            '): ',
        );
    }

    try {
        $value = $fun();

        return [$value, $lastMessage];
    } finally {
        restore_error_handler();
    }
}
