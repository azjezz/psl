<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;

/**
 * @psalm-immutable
 */
final class PathExpression
{
    /**
     * @pure
     */
    public static function path(mixed $path): string
    {
        return match (true) {
            is_bool($path) => $path ? 'true' : 'false',
            is_scalar($path) => (string) $path,
            default => get_debug_type($path),
        };
    }

    /**
     * @pure
     */
    public static function expression(string $expression, mixed $path): string
    {
        return Str\format($expression, self::path($path));
    }

    /**
     * @pure
     */
    public static function iteratorKey(mixed $key): string
    {
        return self::expression('key(%s)', $key);
    }

    /**
     * @pure
     */
    public static function iteratorError(mixed $previousKey): string
    {
        return self::expression($previousKey === null ? 'first()' : '%s.next()', $previousKey);
    }
}
