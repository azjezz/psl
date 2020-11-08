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
use function strpos;
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
    public const CONSTANTS = [
        'Psl\Internal\ALPHABET_BASE64',
        'Psl\Internal\ALPHABET_BASE64_URL',
        'Psl\Internal\CASE_FOLD',
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
        'Psl\Math\NAN',
        'Psl\Str\ALPHABET',
        'Psl\Str\ALPHABET_ALPHANUMERIC',
        'Psl\Password\DEFAULT_ALGORITHM',
        'Psl\Password\BCRYPT_ALGORITHM',
    ];

    public const FUNCTIONS = [
        'Psl\Arr\at',
        'Psl\Arr\concat',
        'Psl\Arr\contains',
        'Psl\Arr\contains_key',
        'Psl\Arr\count',
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
        'Psl\Arr\drop',
        'Psl\Arr\drop_while',
        'Psl\Arr\slice',
        'Psl\Arr\take',
        'Psl\Arr\take_while',
        'Psl\Arr\filter',
        'Psl\Arr\filter_keys',
        'Psl\Arr\filter_nulls',
        'Psl\Arr\filter_with_key',
        'Psl\Arr\map',
        'Psl\Arr\map_keys',
        'Psl\Arr\map_with_key',
        'Psl\Fun\after',
        'Psl\Fun\identity',
        'Psl\Fun\pipe',
        'Psl\Fun\rethrow',
        'Psl\Fun\when',
        'Psl\Internal\boolean',
        'Psl\Internal\type',
        'Psl\Internal\validate_offset',
        'Psl\Internal\validate_offset_lower_bound',
        'Psl\Internal\internal_encoding',
        'Psl\Internal\is_encoding_valid',
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
        'Psl\Iter\keys',
        'Psl\Iter\last',
        'Psl\Iter\last_key',
        'Psl\Iter\map',
        'Psl\Iter\map_keys',
        'Psl\Iter\map_with_key',
        'Psl\Iter\merge',
        'Psl\Iter\product',
        'Psl\Iter\pull',
        'Psl\Iter\pull_with_key',
        'Psl\Iter\random',
        'Psl\Iter\range',
        'Psl\Iter\reduce',
        'Psl\Iter\reduce_keys',
        'Psl\Iter\reduce_with_keys',
        'Psl\Iter\reductions',
        'Psl\Iter\reindex',
        'Psl\Iter\repeat',
        'Psl\Iter\reproduce',
        'Psl\Iter\reverse',
        'Psl\Iter\rewindable',
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
        'Psl\Result\wrap',
        'Psl\SecureRandom\bytes',
        'Psl\SecureRandom\float',
        'Psl\SecureRandom\int',
        'Psl\SecureRandom\string',
        'Psl\PseudoRandom\float',
        'Psl\PseudoRandom\int',
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
        'Psl\Str\Byte\search_last_ci',
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
        'Psl\Str\Byte\after',
        'Psl\Str\Byte\after_ci',
        'Psl\Str\Byte\after_last',
        'Psl\Str\Byte\after_last_ci',
        'Psl\Str\Byte\before',
        'Psl\Str\Byte\before_ci',
        'Psl\Str\Byte\before_last',
        'Psl\Str\Byte\before_last_ci',
        'Psl\Str\capitalize',
        'Psl\Str\capitalize_words',
        'Psl\Str\chr',
        'Psl\Str\chunk',
        'Psl\Str\concat',
        'Psl\Str\contains',
        'Psl\Str\contains_ci',
        'Psl\Str\detect_encoding',
        'Psl\Str\convert_encoding',
        'Psl\Str\is_utf8',
        'Psl\Str\ends_with',
        'Psl\Str\ends_with_ci',
        'Psl\Str\fold',
        'Psl\Str\format',
        'Psl\Str\format_number',
        'Psl\Str\from_code_points',
        'Psl\Str\is_empty',
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
        'Psl\Str\after',
        'Psl\Str\after_ci',
        'Psl\Str\after_last',
        'Psl\Str\after_last_ci',
        'Psl\Str\before',
        'Psl\Str\before_ci',
        'Psl\Str\before_last',
        'Psl\Str\before_last_ci',
        'Psl\invariant',
        'Psl\invariant_violation',
        'Psl\sequence',
        'Psl\Type\map',
        'Psl\Type\mutable_map',
        'Psl\Type\vector',
        'Psl\Type\mutable_vector',
        'Psl\Type\array_key',
        'Psl\Type\bool',
        'Psl\Type\float',
        'Psl\Type\int',
        'Psl\Type\intersection',
        'Psl\Type\iterable',
        'Psl\Type\mixed',
        'Psl\Type\null',
        'Psl\Type\nullable',
        'Psl\Type\num',
        'Psl\Type\object',
        'Psl\Type\resource',
        'Psl\Type\string',
        'Psl\Type\scalar',
        'Psl\Type\union',
        'Psl\Type\is_array',
        'Psl\Type\is_arraykey',
        'Psl\Type\is_bool',
        'Psl\Type\is_callable',
        'Psl\Type\is_float',
        'Psl\Type\is_instanceof',
        'Psl\Type\is_int',
        'Psl\Type\is_iterable',
        'Psl\Type\is_nan',
        'Psl\Type\is_null',
        'Psl\Type\is_numeric',
        'Psl\Type\is_object',
        'Psl\Type\is_resource',
        'Psl\Type\is_scalar',
        'Psl\Type\is_string',
        'Psl\Json\encode',
        'Psl\Json\decode',
        'Psl\Json\typed',
        'Psl\Env\args',
        'Psl\Env\current_dir',
        'Psl\Env\current_exec',
        'Psl\Env\get_var',
        'Psl\Env\get_vars',
        'Psl\Env\join_paths',
        'Psl\Env\remove_var',
        'Psl\Env\set_current_dir',
        'Psl\Env\set_var',
        'Psl\Env\split_paths',
        'Psl\Env\temp_dir',
        'Psl\Password\algorithms',
        'Psl\Password\get_information',
        'Psl\Password\hash',
        'Psl\Password\needs_rehash',
        'Psl\Password\verify',
        'Psl\Hash\hash',
        'Psl\Hash\algorithms',
        'Psl\Hash\equals',
        'Psl\Hash\Hmac\hash',
        'Psl\Hash\Hmac\algorithms',
        'Psl\Str\Grapheme\contains',
        'Psl\Str\Grapheme\contains_ci',
        'Psl\Str\Grapheme\ends_with',
        'Psl\Str\Grapheme\ends_with_ci',
        'Psl\Str\Grapheme\length',
        'Psl\Str\Grapheme\search',
        'Psl\Str\Grapheme\search_ci',
        'Psl\Str\Grapheme\search_last',
        'Psl\Str\Grapheme\search_last_ci',
        'Psl\Str\Grapheme\slice',
        'Psl\Str\Grapheme\starts_with',
        'Psl\Str\Grapheme\starts_with_ci',
        'Psl\Str\Grapheme\strip_prefix',
        'Psl\Str\Grapheme\strip_suffix',
        'Psl\Str\Grapheme\after',
        'Psl\Str\Grapheme\after_ci',
        'Psl\Str\Grapheme\after_last',
        'Psl\Str\Grapheme\after_last_ci',
        'Psl\Str\Grapheme\before',
        'Psl\Str\Grapheme\before_ci',
        'Psl\Str\Grapheme\before_last',
        'Psl\Str\Grapheme\before_last_ci',
    ];

    public const INTERFACES = [
        'Psl\Exception\ExceptionInterface',
        'Psl\Collection\CollectionInterface',
        'Psl\Collection\IndexAccessInterface',
        'Psl\Collection\MutableCollectionInterface',
        'Psl\Collection\MutableIndexAccessInterface',
        'Psl\Collection\AccessibleCollectionInterface',
        'Psl\Collection\MutableAccessibleCollectionInterface',
        'Psl\Collection\VectorInterface',
        'Psl\Collection\MutableVectorInterface',
        'Psl\Collection\MapInterface',
        'Psl\Collection\MutableMapInterface',
        'Psl\Observer\SubjectInterface',
        'Psl\Observer\ObserverInterface',
        'Psl\Result\ResultInterface',
    ];

    public const TRAITS = [
        'Psl\Type\Internal\TypeTraceTrait',
    ];

    public const CLASSES = [
        'Psl\Exception\InvariantViolationException',
        'Psl\Iter\Iterator',
        'Psl\Collection\Vector',
        'Psl\Collection\MutableVector',
        'Psl\Collection\Map',
        'Psl\Collection\MutableMap',
        'Psl\Exception\InvalidArgumentException',
        'Psl\Exception\RuntimeException',
        'Psl\Exception\InvariantViolationException',
        'Psl\Result\Failure',
        'Psl\Result\Success',
        'Psl\Type\Internal\ArrayKeyType',
        'Psl\Type\Internal\MapType',
        'Psl\Type\Internal\MutableMapType',
        'Psl\Type\Internal\VectorType',
        'Psl\Type\Internal\MutableVectorType',
        'Psl\Type\Internal\BoolType',
        'Psl\Type\Internal\FloatType',
        'Psl\Type\Internal\IntersectionType',
        'Psl\Type\Internal\IntType',
        'Psl\Type\Internal\IterableType',
        'Psl\Type\Internal\MixedType',
        'Psl\Type\Internal\NullType',
        'Psl\Type\Internal\NumType',
        'Psl\Type\Internal\ObjectType',
        'Psl\Type\Internal\ResourceType',
        'Psl\Type\Internal\StringType',
        'Psl\Type\Internal\UnionType',
        'Psl\Type\Exception\TypeTrace',
        'Psl\Type\Exception\AssertException',
        'Psl\Type\Exception\CoercionException',
        'Psl\Type\Exception\Exception',
        'Psl\Type\Type',
        'Psl\Json\Exception\DecodeException',
        'Psl\Json\Exception\EncodeException',
        'Psl\Hash\Context',
    ];

    private const TYPE_CONSTANTS = 1;

    private const TYPE_FUNCTION = 2;

    private const TYPE_INTERFACE = 4;

    private const TYPE_TRAIT = 8;

    private const TYPE_CLASS = 16;

    private const TYPE_CLASSISH = self::TYPE_INTERFACE | self::TYPE_TRAIT | self::TYPE_CLASS;

    private function __construct()
    {
    }

    public static function bootstrap(): void
    {
        if (!function_exists(self::FUNCTIONS[0])) {
            self::preload();
        } elseif (!defined(self::CONSTANTS[0])) {
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

    private static function load(string $typename, int $type): void
    {
        $file = self::getFile($typename, $type);

        require_once $file;
    }

    private static function autoload(callable $callback): void
    {
        $loader = static function (string $classname): ?bool {
            if ('P' === $classname[0] && 0 === strpos($classname, 'Psl\\')) {
                static::load($classname, self::TYPE_CLASSISH);

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
        foreach (self::CONSTANTS as $constant) {
            if (defined($constant)) {
                continue;
            }

            self::load($constant, self::TYPE_CONSTANTS);
        }
    }

    private static function loadFunctions(): void
    {
        foreach (self::FUNCTIONS as $function) {
            if (function_exists($function)) {
                continue;
            }

            self::load($function, self::TYPE_FUNCTION);
        }
    }

    private static function loadInterfaces(): void
    {
        foreach (self::INTERFACES as $interface) {
            if (interface_exists($interface)) {
                continue;
            }

            self::load($interface, self::TYPE_INTERFACE);
        }
    }

    private static function loadTraits(): void
    {
        foreach (self::TRAITS as $trait) {
            if (trait_exists($trait)) {
                continue;
            }

            self::load($trait, self::TYPE_TRAIT);
        }
    }

    private static function loadClasses(): void
    {
        foreach (self::CLASSES as $class) {
            if (class_exists($class)) {
                continue;
            }

            self::load($class, self::TYPE_CLASS);
        }
    }

    private static function getFile(string $typename, int $type): string
    {
        $lastSeparatorPosition = strrpos($typename, '\\');
        $namespace = substr($typename, 0, $lastSeparatorPosition);

        if (($type & self::TYPE_CLASSISH) === $type || (($type & self::TYPE_FUNCTION) === $type)) {
            $file = substr($typename, $lastSeparatorPosition + 1) . '.php';
        } else {
            $file = 'constants.php';
        }

        $file = str_replace('\\', '/', $namespace) . '/' . $file;

        return dirname(__DIR__, 2) . '/' . $file;
    }
}
