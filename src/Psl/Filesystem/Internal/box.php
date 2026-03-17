<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Closure;

use function mb_strtolower;
use function restore_error_handler;
use function set_error_handler;
use function str_contains;
use function strpos;
use function substr;

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

    if (null !== $lastMessage && str_contains($lastMessage, '): ')) {
        // how i feel toward PHP error handling:
        $lower = mb_strtolower($lastMessage);
        /** @var non-negative-int $pos */
        $pos = strpos($lower, '): ');
        $lastMessage = substr($lower, $pos + 3);
    }

    try {
        $value = $fun();

        return [$value, $lastMessage];
    } finally {
        restore_error_handler();
    }
}
