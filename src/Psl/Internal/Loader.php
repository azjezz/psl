<?php

declare(strict_types=1);

namespace Psl\Internal;

use function class_exists;
use function defined;
use function dirname;
use function function_exists;
use function interface_exists;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function str_replace;
use function strrpos;
use function substr;
use function trait_exists;

/**
 * This class SHOULD NOT use any Psl functions, or classes.
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
final class Loader
{
    public const Constants = [
        'Psl\Internal\ALPHABET_BASE64',
        'Psl\Internal\ALPHABET_BASE64_URL',
        'Psl\Math\INT64_MAX',
        'Psl\Math\INT64_MIN',
        'Psl\Math\INT53_MAX',
        'Psl\Math\INT53_MIN',
        'Psl\Math\INT32_MAX',
        'Psl\Math\INT32_MIN',
        'Psl\Math\INT16_MAX',
        'Psl\Math\INT16_MIN',
        'Psl\Math\UINT32_MAX',
        'Psl\Math\UINT16_MAX',
        'Psl\Math\PI',
        'Psl\Math\E',
        'Psl\Math\INFINITY',
        'Psl\Math\NaN',
        'Psl\Str\ALPHABET',
        'Psl\Str\ALPHABET_ALPHANUMERIC',
    ];

    public const Functions = [
        'Psl\Arr\at',
        'Psl\Arr\concat',
        'Psl\Arr\contains',
        'Psl\Arr\contains_key',
        'Psl\Arr\count_values',
        'Psl\Arr\equal',
        'Psl\Arr\fill',
        'Psl\Arr\first',
        'Psl\Arr\first_key',
        'Psl\Arr\firstx',
        'Psl\Arr\flatten',
        'Psl\Arr\flip',
        'Psl\Arr\group_by',
        'Psl\Arr\idx',
        'Psl\Arr\is_array',
        'Psl\Arr\is_arraykey',
        'Psl\Arr\keys',
        'Psl\Arr\last',
        'Psl\Arr\last_key',
        'Psl\Arr\lastx',
        'Psl\Arr\merge',
        'Psl\Arr\partition',
        'Psl\Arr\random',
        'Psl\Arr\select_keys',
        'Psl\Arr\shuffle',
        'Psl\Arr\sort',
        'Psl\Arr\sort_by',
        'Psl\Arr\sort_by_key',
        'Psl\Arr\sort_with_keys',
        'Psl\Arr\sort_with_keys_by',
        'Psl\Arr\unique',
        'Psl\Arr\unique_by',
        'Psl\Arr\values',
        'Psl\Asio\wrap',
        'Psl\Gen\chain',
        'Psl\Gen\chunk',
        'Psl\Gen\chunk_with_keys',
        'Psl\Gen\diff_by_key',
        'Psl\Gen\drop',
        'Psl\Gen\drop_while',
        'Psl\Gen\enumerate',
        'Psl\Gen\filter',
        'Psl\Gen\filter_keys',
        'Psl\Gen\filter_nulls',
        'Psl\Gen\filter_with_key',
        'Psl\Gen\flatten',
        'Psl\Gen\flip',
        'Psl\Gen\from_entries',
        'Psl\Gen\from_keys',
        'Psl\Gen\keys',
        'Psl\Gen\map',
        'Psl\Gen\map_keys',
        'Psl\Gen\map_with_key',
        'Psl\Gen\product',
        'Psl\Gen\pull',
        'Psl\Gen\pull_with_key',
        'Psl\Gen\range',
        'Psl\Gen\reductions',
        'Psl\Gen\reindex',
        'Psl\Gen\repeat',
        'Psl\Gen\reverse',
        'Psl\Gen\rewindable',
        'Psl\Gen\slice',
        'Psl\Gen\take',
        'Psl\Gen\take_while',
        'Psl\Gen\values',
        'Psl\Gen\zip',
        'Psl\Internal\boolean',
        'Psl\Internal\type',
        'Psl\Internal\validate_offset',
        'Psl\Internal\validate_offset_lower_bound',
        'Psl\Iter\all',
        'Psl\Iter\any',
        'Psl\Iter\apply',
        'Psl\Iter\chain',
        'Psl\Iter\chunk',
        'Psl\Iter\chunk_with_keys',
        'Psl\Iter\contains',
        'Psl\Iter\contains_key',
        'Psl\Iter\count',
        'Psl\Iter\diff_by_key',
        'Psl\Iter\drop',
        'Psl\Iter\drop_while',
        'Psl\Iter\enumerate',
        'Psl\Iter\filter',
        'Psl\Iter\filter_keys',
        'Psl\Iter\filter_nulls',
        'Psl\Iter\filter_with_key',
        'Psl\Iter\first',
        'Psl\Iter\first_key',
        'Psl\Iter\flatten',
        'Psl\Iter\flip',
        'Psl\Iter\from_entries',
        'Psl\Iter\from_keys',
        'Psl\Iter\is_empty',
        'Psl\Iter\is_iterable',
        'Psl\Iter\keys',
        'Psl\Iter\last',
        'Psl\Iter\last_key',
        'Psl\Iter\map',
        'Psl\Iter\map_keys',
        'Psl\Iter\map_with_key',
        'Psl\Iter\product',
        'Psl\Iter\pull',
        'Psl\Iter\pull_with_key',
        'Psl\Iter\random',
        'Psl\Iter\range',
        'Psl\Iter\reduce',
        'Psl\Iter\reductions',
        'Psl\Iter\reindex',
        'Psl\Iter\repeat',
        'Psl\Iter\reverse',
        'Psl\Iter\search',
        'Psl\Iter\slice',
        'Psl\Iter\take',
        'Psl\Iter\take_while',
        'Psl\Iter\to_array',
        'Psl\Iter\to_array_with_keys',
        'Psl\Iter\to_iterator',
        'Psl\Iter\values',
        'Psl\Iter\zip',
        'Psl\Math\abs',
        'Psl\Math\base_convert',
        'Psl\Math\ceil',
        'Psl\Math\cos',
        'Psl\Math\div',
        'Psl\Math\exp',
        'Psl\Math\floor',
        'Psl\Math\from_base',
        'Psl\Math\log',
        'Psl\Math\max',
        'Psl\Math\max_by',
        'Psl\Math\maxva',
        'Psl\Math\mean',
        'Psl\Math\median',
        'Psl\Math\min',
        'Psl\Math\min_by',
        'Psl\Math\minva',
        'Psl\Math\round',
        'Psl\Math\sin',
        'Psl\Math\sqrt',
        'Psl\Math\sum',
        'Psl\Math\sum_floats',
        'Psl\Math\tan',
        'Psl\Math\to_base',
        'Psl\Random\bytes',
        'Psl\Random\float',
        'Psl\Random\int',
        'Psl\Random\string',
        'Psl\Str\Byte\capitalize',
        'Psl\Str\Byte\capitalize_words',
        'Psl\Str\Byte\chr',
        'Psl\Str\Byte\chunk',
        'Psl\Str\Byte\compare',
        'Psl\Str\Byte\compare_ci',
        'Psl\Str\Byte\contains',
        'Psl\Str\Byte\contains_ci',
        'Psl\Str\Byte\ends_with',
        'Psl\Str\Byte\ends_with_ci',
        'Psl\Str\Byte\length',
        'Psl\Str\Byte\lowercase',
        'Psl\Str\Byte\ord',
        'Psl\Str\Byte\pad_left',
        'Psl\Str\Byte\pad_right',
        'Psl\Str\Byte\replace',
        'Psl\Str\Byte\replace_ci',
        'Psl\Str\Byte\replace_every',
        'Psl\Str\Byte\replace_every_ci',
        'Psl\Str\Byte\reverse',
        'Psl\Str\Byte\rot13',
        'Psl\Str\Byte\search',
        'Psl\Str\Byte\search_ci',
        'Psl\Str\Byte\search_last',
        'Psl\Str\Byte\shuffle',
        'Psl\Str\Byte\slice',
        'Psl\Str\Byte\splice',
        'Psl\Str\Byte\split',
        'Psl\Str\Byte\starts_with',
        'Psl\Str\Byte\starts_with_ci',
        'Psl\Str\Byte\strip_prefix',
        'Psl\Str\Byte\strip_suffix',
        'Psl\Str\Byte\trim',
        'Psl\Str\Byte\trim_left',
        'Psl\Str\Byte\trim_right',
        'Psl\Str\Byte\uppercase',
        'Psl\Str\Byte\words',
        'Psl\Str\Byte\wrap',
        'Psl\Str\capitalize',
        'Psl\Str\capitalize_words',
        'Psl\Str\chr',
        'Psl\Str\chunk',
        'Psl\Str\concat',
        'Psl\Str\contains',
        'Psl\Str\contains_ci',
        'Psl\Str\encoding',
        'Psl\Str\ends_with',
        'Psl\Str\ends_with_ci',
        'Psl\Str\fold',
        'Psl\Str\format',
        'Psl\Str\format_number',
        'Psl\Str\from_code_points',
        'Psl\Str\is_empty',
        'Psl\Str\is_string',
        'Psl\Str\join',
        'Psl\Str\length',
        'Psl\Str\levenshtein',
        'Psl\Str\lowercase',
        'Psl\Str\metaphone',
        'Psl\Str\ord',
        'Psl\Str\pad_left',
        'Psl\Str\pad_right',
        'Psl\Str\repeat',
        'Psl\Str\replace',
        'Psl\Str\replace_ci',
        'Psl\Str\replace_every',
        'Psl\Str\replace_every_ci',
        'Psl\Str\search',
        'Psl\Str\search_ci',
        'Psl\Str\search_last',
        'Psl\Str\search_last_ci',
        'Psl\Str\slice',
        'Psl\Str\splice',
        'Psl\Str\split',
        'Psl\Str\starts_with',
        'Psl\Str\starts_with_ci',
        'Psl\Str\strip_prefix',
        'Psl\Str\strip_suffix',
        'Psl\Str\to_int',
        'Psl\Str\trim',
        'Psl\Str\trim_left',
        'Psl\Str\trim_right',
        'Psl\Str\truncate',
        'Psl\Str\uppercase',
        'Psl\Str\width',
        'Psl\Str\wrap',
        'Psl\invariant',
        'Psl\invariant_violation',
        'Psl\sequence',
    ];

    public const Interfaces = [
        'Psl\Exception\ExceptionInterface',
        'Psl\Asio\IResultOrExceptionWrapper',
        'Psl\Collection\ICollection',
        'Psl\Collection\IIndexAccess',
        'Psl\Collection\IMutableCollection',
        'Psl\Collection\IMutableIndexAccess',
        'Psl\Collection\IAccessibleCollection',
        'Psl\Collection\IMutableAccessibleCollection',
        'Psl\Collection\IVector',
        'Psl\Collection\IMutableVector',
        'Psl\Collection\IMap',
        'Psl\Collection\IMutableMap',
    ];

    public const Traits = [

    ];

    public const Classes = [
        'Psl\Exception\InvariantViolationException',
        'Psl\Iter\Iterator',
        'Psl\Collection\Vector',
        'Psl\Collection\MutableVector',
        'Psl\Collection\Map',
        'Psl\Collection\MutableMap',
        'Psl\Gen\RewindableGenerator',
        'Psl\Asio\WrappedException',
        'Psl\Asio\WrappedResult',
    ];
    private const TypeConstant = 1;
    private const TypeFunction = 2;
    private const TypeInterface = 4;
    private const TypeTrait = 8;
    private const TypeClass = 16;

    private const TypeClassish = self::TypeInterface | self::TypeTrait | self::TypeClass;

    private function __construct()
    {
    }

    public static function bootstrap(): void
    {
        if (!function_exists(self::Functions[0])) {
            self::preload();
        } elseif (!defined(self::Constants[0])) {
            self::loadConstants();
        }
    }

    public static function preload(): void
    {
        static::loadConstants();
        static::autoload(static function (): void {
            static::loadFunctions();
            static::loadInterfaces();
            static::loadTraits();
            static::loadClasses();
        });
    }

    private static function autoload(callable $callback): void
    {
        $loader = static function (string $classname): ?bool {
            if ('P' === $classname[0] && 0 === \strpos($classname, 'Psl\\')) {
                require_once static::getFile($classname, self::TypeClassish);

                return true;
            }

            return null;
        };

        spl_autoload_register($loader);
        $callback();
        spl_autoload_unregister($loader);
    }

    private static function loadConstants(): void
    {
        foreach (self::Constants as $constant) {
            if (defined($constant)) {
                continue;
            }

            self::load($constant, self::TypeConstant);
        }
    }

    private static function loadFunctions(): void
    {
        foreach (self::Functions as $function) {
            if (function_exists($function)) {
                continue;
            }

            self::load($function, self::TypeFunction);
        }
    }

    private static function loadInterfaces(): void
    {
        foreach (self::Interfaces as $interface) {
            if (interface_exists($interface)) {
                continue;
            }

            self::load($interface, self::TypeInterface);
        }
    }

    private static function loadTraits(): void
    {
        foreach (self::Traits as $trait) {
            if (trait_exists($trait)) {
                continue;
            }

            self::load($trait, self::TypeTrait);
        }
    }

    private static function loadClasses(): void
    {
        foreach (self::Classes as $class) {
            if (class_exists($class)) {
                continue;
            }

            self::load($class, self::TypeClass);
        }
    }

    private static function load(string $typename, int $type): void
    {
        $file = self::getFile($typename, $type);

        require_once $file;
    }

    private static function getFile(string $typename, int $type): string
    {
        $lastSeparatorPosition = strrpos($typename, '\\');
        $namespace = substr($typename, 0, $lastSeparatorPosition);

        if (($type & self::TypeClassish) === $type || (($type & self::TypeFunction) === $type)) {
            $file = substr($typename, $lastSeparatorPosition + 1) . '.php';
        } else {
            $file = 'constants.php';
        }

        $file = str_replace('\\', '/', $namespace) . '/' . $file;

        return dirname(__DIR__, 2) . '/' . $file;
    }
}
