<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Vec;

use function is_array;
use function is_int;
use function is_iterable;

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

    /**
     * @param array<Tk, Type\TypeInterface<Tv>> $elements_types
     */
    public function __construct(array $elements_types)
    {
        $this->elements_types = $elements_types;
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
        if (is_iterable($value)) {
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
                $element_name = is_int($element) ? (string) $element : Str\format('\'%s\'', $element);
                $trace =  $this->getTrace()->withFrame('array{' . $element_name . ': _}');
                /** @var Type\Type<Tv> $type */
                $type = $type->withTrace($trace);
                if (Iter\contains_key($array, $element)) {
                    $result[$element] = $type->coerce($array[$element]);

                    continue;
                }

                throw CoercionException::withValue($value, $this->toString(), $trace);
            }

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
        if (is_array($value)) {
            /** @var list<array-key> $value_keys */
            $value_keys = Vec\sort(Vec\keys($value));

            $elements = Vec\sort(Vec\keys($this->elements_types));
            if ($elements !== $value_keys) {
                throw AssertException::withValue($value, $this->toString(), $this->getTrace());
            }

            $result = [];
            foreach ($this->elements_types as $element => $type) {
                $element_name = is_int($element) ? (string) $element : Str\format('\'%s\'', $element);
                $trace =  $this->getTrace()->withFrame('array{' . $element_name . ': _}');
                /** @var Type\Type<Tv> $type */
                $type = $type->withTrace($trace);
                if (Iter\contains_key($value, $element)) {
                    $result[$element] = $type->assert($value[$element]);

                    continue;
                }

                throw AssertException::withValue($value, $this->toString(), $trace);
            }

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
            $node = is_int($element) ? (string) $element : Str\format('\'%s\'', $element);
            $node .= ': ';
            $node .= $type->toString();

            $nodes[] = $node;
        }

        return Str\format('array{%s}', Str\join($nodes, ', '));
    }
}
