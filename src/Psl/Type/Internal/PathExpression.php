<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;

/**
 * @psalm-immutable
 *
 * @internal
 *
 * This class can be used for building the "path" parts of an exception message.
 * It is introduced to make sure the same path formatting is used when constructing the exception message.
 */
final readonly class PathExpression
{
    /**
     * @pure
     *
     * In most situations, the $path will be an array-key (string|int) that represents the position of the value in the data structure.
     * When using iterators, this could be any type.
     *
     * This function will always return a string representation of the path:
     * - for booleans, it will return "true" or "false"
     * - for scalars, it will return the string representation of the scalar (int, float, string)
     * - for other types, it will return the debug type of the value
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
     *
     * This function can be used to display the path in a very specific way.
     * For example:
     * - expression 'key(%s)' will display the path as "key(0)" when path is 0.
     * - expression 'key(%s)' will display the path as "key(someIndex)" when path is "someIndex".
     *
     * In most situations, the $path will be an array-key (string|int) that represents the position of the value in the data structure.
     * When using iterators, this could be any type.
     */
    public static function expression(string $expression, mixed $path): string
    {
        return Str\format($expression, self::path($path));
    }

    /**
     * @pure
     *
     * This function must be used to format the path when parsing the key of an iterator fails.
     *
     * In most situations, the $path will be an array-key (string|int) that represents the position of the value in the data structure.
     * When using iterators, this could be any type.
     */
    public static function iteratorKey(mixed $key): string
    {
        return self::expression('key(%s)', $key);
    }

    /**
     * @pure
     *
     * This function must be used to format the path when an internal error occurs when iterating an iterator - like for example a \Generator.
     *
     * This function takes the value of the $previousKey as an argument.
     * If a previous key is known, the result will formatted as : previousKey.next().
     * If no previous key is known, the result will formatted as : first().
     */
    public static function iteratorError(mixed $previousKey): string
    {
        return self::expression($previousKey === null ? 'first()' : '%s.next()', $previousKey);
    }

    /**
     * @pure
     *
     * This function must be used to format the path when coercing a mixed input to a specific type fails.
     *
     * The first $input argument is used to display the type you are trying to coerce.
     * The second $expectedType argument is used to display the type you are trying to coerce into.
     *
     * Example output:
     * - **coerce_input(string): int**: This means that the input 'string' could not be coerced to the expected output 'int'.
     */
    public static function coerceInput(mixed $input, string $expectedType): string
    {
        return Str\format('coerce_input(%s): %s', get_debug_type($input), $expectedType);
    }

    /**
     * @pure
     *
     * This function must be used to format the path when converting an input by using a custom converter fails.
     *
     * The first $input argument is used to display the type you are trying to convert.
     * The second $expectedType argument is used to display the type you are trying to convert into.
     *
     * Example output:
     * - **convert(string): int**: This means that the input 'string' could not be converted to the expected output 'int'.
     */
    public static function convert(mixed $input, string $expectedType): string
    {
        return Str\format('convert(%s): %s', get_debug_type($input), $expectedType);
    }

    /**
     * @pure
     *
     * This function must be used to format the path when coercing a mixed output to a specific type fails.
     *
     * The first $input argument is used to display the type you are trying to coerce.
     * The second $expectedType argument is used to display the type you are trying to coerce into.
     *
     * Example output:
     * - **coerce_output(string): int**: This means that the input 'string' could not be coerced to the expected output 'int'.
     */
    public static function coerceOutput(mixed $input, string $expectedType): string
    {
        return Str\format('coerce_output(%s): %s', get_debug_type($input), $expectedType);
    }
}
