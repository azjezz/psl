<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 */
final class ShapeType extends Type\Type
{
    /** @var array<string, null> */
    private array $requiredElements;

    /**
     * @param array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    public function __construct(
        private array $elements_types,
        private bool $allow_unknown_fields = false,
    ) {
        $this->requiredElements = \array_filter(
            $elements_types,
            static fn (Type\TypeInterface $element): bool => ! $element->isOptional()
        );
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    public function coerce(mixed $value): array
    {
        // To whom reads this: yes, I hate this stuff as passionately as you do :-)
        if (! \is_array($value)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (\array_keys(\array_intersect_key($value, $this->requiredElements)) !== \array_keys($this->requiredElements)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        if (! $this->allow_unknown_fields && \array_keys($value) !== \array_keys($this->elements_types)) {
            // Fallback to slow implementation - unhappy path
            return $this->coerceIterable($value);
        }

        $coerced = [];

        try {
            foreach (\array_intersect_key($this->elements_types, $value) as $key => $type) {
                $coerced[$key] = $type->coerce($value[$key]);
            }
        } catch (CoercionException) {
            // Fallback to slow implementation - unhappy path. Prevents having to eagerly compute traces.
            $this->coerceIterable($value);
        }

        foreach (\array_diff_key($value, $this->elements_types) as $key => $additionalValue) {
            $coerced[$key] = $additionalValue;
        }

        return $coerced;
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    private function coerceIterable(mixed $value): array
    {
        /** @psalm-suppress MissingThrowsDocblock */
        if (Type\iterable(Type\mixed(), Type\mixed())->matches($value)) {
            $array = [];
            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                if (Type\array_key()->matches($k)) {
                    $array[$k] = $v;
                }
            }

            $result = [];
            foreach ($this->elements_types as $element => $type) {
                [$trace, $type] = $this->getTypeAndTraceForElement($element, $type);
                if (Iter\contains_key($array, $element)) {
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
                    if (!Iter\contains_key($result, $k)) {
                        $result[$k] = $v;
                    }
                }
            }

            /** @var array<Tk, Tv> */
            return $result;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     */
    public function assert(mixed $value): array
    {
        /** @psalm-suppress MissingThrowsDocblock */
        if (Type\dict(Type\array_key(), Type\mixed())->matches($value)) {
            $result = [];
            foreach ($this->elements_types as $element => $type) {
                [$trace, $type] = $this->getTypeAndTraceForElement($element, $type);
                if (Iter\contains_key($value, $element)) {
                    $result[$element] = $type->assert($value[$element]);

                    continue;
                }

                if ($type->isOptional()) {
                    continue;
                }

                throw AssertException::withValue($value, $this->toString(), $trace);
            }

            /**
             * @var Tk $k
             * @var Tv $v
             */
            foreach ($value as $k => $v) {
                if (!Iter\contains_key($result, $k)) {
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

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * Returns a string representation of the shape.
     */
    public function toString(): string
    {
        $nodes = [];
        foreach ($this->elements_types as $element => $type) {
            $node = $this->getElementName($element);
            $node .= $type->isOptional() ? '?' : '';
            $node .= ': ';
            $node .= $type->toString();

            $nodes[] = $node;
        }

        return Str\format('array{%s}', Str\join($nodes, ', '));
    }

    private function getElementName(string|int $element): string
    {
        return \is_int($element)
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
            $type->withTrace($trace)
        ];
    }
}
