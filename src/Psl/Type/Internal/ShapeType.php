<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function array_diff_key;
use function array_filter;
use function array_intersect_key;
use function array_keys;
use function implode;
use function is_array;
use function is_int;
use function is_iterable;
use function is_string;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 */
final class ShapeType extends Type\Type
{
    /**
     * @var array<Tk, Type\TypeInterface<Tv>>
     */
    private array $requiredElements;

    /**
     * @param array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    public function __construct(
        private array $elements_types,
        private bool $allow_unknown_fields = false,
    ) {
        $this->requiredElements = array_filter(
            $elements_types,
            /**
             * @param Type\TypeInterface<Tv> $element
             */
            static fn (Type\TypeInterface $element): bool => ! $element->isOptional()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function coerce(mixed $value): array
    {
        // To whom reads this: yes, I hate this stuff as passionately as you do :-)
        if (! is_array($value)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (array_keys(array_intersect_key($value, $this->requiredElements)) !== array_keys($this->requiredElements)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (! $this->allow_unknown_fields && array_keys($value) !== array_keys($this->elements_types)) {
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
     * @param mixed|iterable<Tk, Tv> $value
     *
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    private function coerceIterable(mixed $value): array
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        /**
         * @var iterable<Tk, Tv> $value
         * @var array<Tk, Tv> $array
         */
        $array = [];
        foreach ($value as $k => $v) {
            if (is_int($k) || is_string($k)) {
                $array[$k] = $v;
            }
        }

        $result = [];
        foreach ($this->elements_types as $element => $type) {
            [$trace, $type] = $this->getTypeAndTraceForElement($element, $type);
            if (isset($array[$element])) {
                $result[$element] = $type->coerce($array[$element]);

                continue;
            }

            if ($type->isOptional()) {
                continue;
            }

            throw CoercionException::withValue($value, $this->toString(), $trace);
        }

        if ($this->allow_unknown_fields) {
            foreach ($array as $k => $v) {
                if (!isset($result[$k])) {
                    $result[$k] = $v;
                }
            }
        }

        /** @var array<Tk, Tv> */
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function assert(mixed $value): array
    {
        if (! is_array($value)) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }

        /**
         * @var array<Tk, Tv> $value
         * @var array<Tk, Tv> $result
         */
        $result = [];
        foreach ($this->elements_types as $element => $type) {
            [$trace, $type] = $this->getTypeAndTraceForElement($element, $type);
            if (isset($value[$element])) {
                $result[$element] = $type->assert($value[$element]);

                continue;
            }

            if ($type->isOptional()) {
                continue;
            }

            throw AssertException::withValue($value, $this->toString(), $trace);
        }

        foreach ($value as $k => $v) {
            if (!isset($result[$k])) {
                if ($this->allow_unknown_fields) {
                    $result[$k] = $v;
                } else {
                    throw AssertException::withValue(
                        $value,
                        $this->toString(),
                        $this->getTrace()->withFrame('array{' . $this->getElementName($k) . ': _}')
                    );
                }
            }
        }

        /** @var array<Tk, Tv> */
        return $result;
    }

    /**
     * Returns a string representation of the shape.
     */
    public function toString(): string
    {
        /** @var list<string> $nodes */
        $nodes = [];
        foreach ($this->elements_types as $element => $type) {
            $nodes[] = $this->getElementName($element)
                . ($type->isOptional() ? '?' : '')
                . ': '
                . $type->toString();
        }

        return 'array{' . implode(', ', $nodes) . '}';
    }

    private function getElementName(string|int $element): string
    {
        return is_int($element)
            ? (string) $element
            : '\'' . $element . '\'';
    }

    /**
     * @template T
     *
     * @param Type\TypeInterface<T> $type
     *
     * @return array{0: Type\Exception\TypeTrace, 1: Type\TypeInterface<T>}
     */
    private function getTypeAndTraceForElement(string|int $element, Type\TypeInterface $type): array
    {
        $element_name = $this->getElementName($element);
        $trace = $this->getTrace()->withFrame('array{' . $element_name . ': _}');

        return [
            $trace,
            $type->withTrace($trace),
        ];
    }
}
