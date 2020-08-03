<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Arr;
use Psl\Iter;
use Psl\Str;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends Type<array<Tk, Tv>>
 *
 * @internal
 */
final class ArrayType extends Type
{
    /**
     * @psalm-var Type<Tk>
     */
    private Type $key_type;

    /**
     * @psalm-var Type<Tv>
     */
    private Type $value_type;

    /**
     * @psalm-param Type<Tk> $key_type
     * @psalm-param Type<Tv> $value_type
     */
    public function __construct(
        Type $key_type,
        Type $value_type
    ) {
        $this->key_type = $key_type;
        $this->value_type = $value_type;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return array<Tk, Tv>
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): array
    {
        if (Iter\is_iterable($value)) {
            $key_trace = $this->getTrace()->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<array{0: Tk, 1: Tv}> $entries
             */
            $entries = [];
            /**
             * @psalm-var mixed $k
             * @psalm-var mixed $v
             */
            foreach ($value as $k => $v) {
                /** @psalm-var Tk $k */
                $k = $key_type->coerce($k);
                /** @psalm-var Tv $v */
                $v = $value_type->coerce($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            /** @psalm-var array<Tk, Tv> */
            return Iter\to_array_with_keys($iterator);
        }


        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     *
     * @throws TypeAssertException
     */
    public function assert($value): array
    {
        if (Arr\is_array($value)) {
            $key_trace = $this->getTrace()->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type<Tv> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<array{0: Tk, 1: Tv}> $entries
             */
            $entries = [];
            /**
             * @psalm-var mixed $k
             * @psalm-var mixed $v
             */
            foreach ($value as $k => $v) {
                /** @psalm-var Tk $k */
                $k = $key_type->assert($k);
                /** @psalm-var Tv $v */
                $v = $value_type->assert($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            return Iter\to_array_with_keys($iterator);
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('array<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
