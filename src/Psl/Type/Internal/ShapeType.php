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
    /**
     * @var array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    private array $elements_types;

    private bool $allow_unknown_fields;

    /**
     * @param array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    public function __construct(array $elements_types, bool $allow_unknown_fields = false)
    {
        $this->elements_types = $elements_types;
        $this->allow_unknown_fields = $allow_unknown_fields;
    }

    /**
     * @param mixed $value
     *
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    public function coerce($value): array
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
     * @param mixed $value
     *
     * @throws AssertException
     *
     * @return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     */
    public function assert($value): array
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

    /**
     * @param array-key $element
     */
    private function getElementName($element): string
    {
        return Type\int()->matches($element) ? (string) $element : Str\format('\'%s\'', $element);
    }

    /**
     * @template T
     *
     * @param array-key $element
     * @param Type\TypeInterface<T> $type
     *
     * @return array{0: Type\Exception\TypeTrace, 1: Type\TypeInterface<T>}
     */
    private function getTypeAndTraceForElement($element, Type\TypeInterface $type): array
    {
        $element_name = $this->getElementName($element);
        $trace = $this->getTrace()->withFrame('array{' . $element_name . ': _}');

        return [
            $trace,
            $type->withTrace($trace)
        ];
    }
}
