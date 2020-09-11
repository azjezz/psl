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
 *
 * @internal
 */
final class ArrayType extends Type\Type
{
    /**
     * @psalm-var Type\Type<Tk>
     */
    private Type\Type $key_type;

    /**
     * @psalm-var Type\Type<Tv>
     */
    private Type\Type $value_type;

    /**
     * @psalm-param Type\Type<Tk> $key_type
     * @psalm-param Type\Type<Tv> $value_type
     */
    public function __construct(
        Type\Type $key_type,
        Type\Type $value_type
    ) {
        $this->key_type   = $key_type;
        $this->value_type = $value_type;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return array<Tk, Tv>
     *
     * @throws CoercionException
     */
    public function coerce($value): array
    {
        if (Type\is_iterable($value)) {
            $key_trace   = $this->getTrace()->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type */
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
                $v         = $value_type->coerce($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            /** @psalm-var array<Tk, Tv> */
            return Iter\to_array_with_keys($iterator);
        }


        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return array<Tk, Tv>
     *
     * @psalm-assert array<Tk, Tv> $value
     *
     * @throws AssertException
     */
    public function assert($value): array
    {
        if (Type\is_array($value)) {
            $key_trace   = $this->getTrace()->withFrame(Str\format('array<%s, _>', $this->key_type->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('array<_, %s>', $this->value_type->toString()));

            /** @psalm-var Type\Type<Tk> $key_type */
            $key_type = $this->key_type->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type */
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
                $v         = $value_type->assert($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            return Iter\to_array_with_keys($iterator);
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('array<%s, %s>', $this->key_type->toString(), $this->value_type->toString());
    }
}
