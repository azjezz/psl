<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_array;
use function is_iterable;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type\Type<array<Tk, Tv>>
 *
 * @internal
 */
final class DictType extends Type\Type
{
    /**
     * @param Type\TypeInterface<Tk> $key_type
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $key_type,
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return array<Tk, Tv>
     */
    public function coerce(mixed $value): array
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        $trace = $this->getTrace();
        $key_type = $this->key_type->withTrace($trace->withFrame('dict<' . $this->key_type->toString() . ', _>'));
        $value_type = $this->value_type->withTrace($trace->withFrame('dict<_, ' . $this->value_type->toString() . '>'));

        $result = [];

        /**
         * @var Tk $k
         * @var Tv $v
         */
        foreach ($value as $k => $v) {
            $result[$key_type->coerce($k)] = $value_type->coerce($v);
        }

        return $result;
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
        if (! is_array($value)) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }

        $trace = $this->getTrace();
        $key_type = $this->key_type->withTrace(
            $trace->withFrame('dict<' . $this->key_type->toString() . ', _>')
        );
        $value_type = $this->value_type->withTrace(
            $trace->withFrame('dict<_, ' . $this->value_type->toString() . '>')
        );

        $result = [];

        /**
         * @var Tk $k
         * @var Tv $v
         */
        foreach ($value as $k => $v) {
            $result[$key_type->assert($k)] = $value_type->assert($v);
        }

        return $result;
    }

    public function toString(): string
    {
        return 'dict<' . $this->key_type->toString() . ', ' . $this->value_type->toString() . '>';
    }
}
