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
 *
 * @psalm-suppress MissingThrowsDocblock
 */
function box(Closure $fun): array
{
    $last_message = null;
    /** @psalm-suppress InvalidArgument */
    set_error_handler(static function (int $_type, string $message) use (&$last_message) {
        $last_message = $message;
    });

    if (null !== $last_message && Str\contains($last_message, '): ')) {
        $last_message = Str\after(
            Str\lowercase($last_message),
            // how i feel toward PHP error handling:
            '): '
        );
    }

    try {
        $value = $fun();

        /** @var array{0: T, 1: ?string} $result */
        $result = [$value, $last_message];

        return $result;
    } finally {
        restore_error_handler();
    }
}
