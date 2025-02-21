<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use stdClass;
use Throwable;

use function array_diff_key;
use function array_filter;
use function array_intersect_key;
use function array_keys;
use function implode;
use function is_array;
use function is_int;
use function is_iterable;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 */
final readonly class ShapeType extends Type\Type
{
    /**
     * @var array<Tk, Type\TypeInterface<Tv>>
     */
    private array $requiredElements;

    /**
     * @psalm-mutation-free
     *
     * @param array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    public function __construct(
        private array $elements_types,
        private bool $allow_unknown_fields = false,
    ) {
        /** @psalm-suppress ImpureFunctionCall - This implementation is pure. */
        $this->requiredElements = array_filter(
            $elements_types,
            static fn(Type\TypeInterface $element): bool => !$element->isOptional(),
        );
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    #[\Override]
    public function coerce(mixed $value): array
    {
        if ($value instanceof stdClass) {
            $value = (array) $value;
        }

        // To whom reads this: yes, I hate this stuff as passionately as you do :-)
        if (!is_array($value)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (array_keys(array_intersect_key($value, $this->requiredElements)) !== array_keys($this->requiredElements)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (!$this->allow_unknown_fields && array_keys($value) !== array_keys($this->elements_types)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        $coerced = [];

        try {
            foreach (array_intersect_key($this->elements_types, $value) as $key => $type) {
                $coerced[$key] = $type->coerce($value[$key]);
            }
        } catch (CoercionException) {
            // Fallback to slow implementation - unhappy path. Prevents having to eagerly compute traces.
            $this->coerceIterable($value);
        }

        /** @var mixed $additionalValue */
        foreach (array_diff_key($value, $this->elements_types) as $key => $additionalValue) {
            /** @psalm-suppress MixedAssignment type inference is broken by additional (unknown) fields */
            $coerced[$key] = $additionalValue;
        }

        /** @var array<Tk, Tv> $coerced type inference is broken by additional (unknown) fields */
        return $coerced;
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    private function coerceIterable(mixed $value): array
    {
        if (!is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        $arrayKeyType = Type\array_key();
        $array = [];
        $k = null;
        try {
            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                if ($arrayKeyType->matches($k)) {
                    $array[$k] = $v;
                }
            }
        } catch (Throwable $e) {
            throw CoercionException::withValue(null, $this->toString(), PathExpression::iteratorError($k), $e);
        }

        $result = [];
        $element = null;
        $element_value_found = false;

        try {
            foreach ($this->elements_types as $element => $type) {
                $element_value_found = false;
                if (Iter\contains_key($array, $element)) {
                    $element_value_found = true;
                    $result[$element] = $type->coerce($array[$element]);

                    continue;
                }

                if ($type->isOptional()) {
                    continue;
                }

                throw CoercionException::withValue(null, $this->toString(), PathExpression::path($element));
            }
        } catch (CoercionException $e) {
            throw match (true) {
                $element_value_found => CoercionException::withValue(
                    $array[$element] ?? null,
                    $this->toString(),
                    PathExpression::path($element),
                    $e,
                ),
                default => $e,
            };
        }

        if ($this->allow_unknown_fields) {
            foreach ($array as $k => $v) {
                if (!Iter\contains_key($result, $k)) {
                    $result[$k] = $v;
                }
            }
        }

        /** @var array<Tk, Tv> */
        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     */
    #[\Override]
    public function assert(mixed $value): array
    {
        if (!is_array($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        $result = [];
        $element = null;
        $element_value_found = false;

        try {
            foreach ($this->elements_types as $element => $type) {
                $element_value_found = false;
                if (Iter\contains_key($value, $element)) {
                    $element_value_found = true;
                    $result[$element] = $type->assert($value[$element]);

                    continue;
                }

                if ($type->isOptional()) {
                    continue;
                }

                throw AssertException::withValue(null, $this->toString(), PathExpression::path($element));
            }
        } catch (AssertException $e) {
            throw match (true) {
                $element_value_found => AssertException::withValue(
                    $value[$element] ?? null,
                    $this->toString(),
                    PathExpression::path($element),
                    $e,
                ),
                default => $e,
            };
        }

        /**
         * @var Tk $k
         * @var Tv $v
         */
        foreach ($value as $k => $v) {
            if (!Iter\contains_key($result, $k)) {
                if ($this->allow_unknown_fields) {
                    $result[$k] = $v;
                    continue;
                }

                throw AssertException::withValue($v, $this->toString(), PathExpression::path($k));
            }
        }

        /** @var array<Tk, Tv> */
        return $result;
    }

    /**
     * Returns a string representation of the shape.
     */
    #[\Override]
    public function toString(): string
    {
        $nodes = [];
        foreach ($this->elements_types as $element => $type) {
            $nodes[] = $this->getElementName($element) . ($type->isOptional() ? '?' : '') . ': ' . $type->toString();
        }

        return 'array{' . implode(', ', $nodes) . '}';
    }

    private function getElementName(string|int $element): string
    {
        return is_int($element) ? ((string) $element) : ('\'' . $element . '\'');
    }
}
