<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Type\Exception\TypeAssertException;
use Psl\Type\Exception\TypeCoercionException;
use Psl\Type;

/**
 * @template Tk
 * @template Tv
 *
 * @extends Type\Type<iterable<Tk, Tv>>
 *
 * @internal
 */
final class IterableType extends Type\Type
{
    /**
     * @psalm-var Type\Type<Tk>
     */
    private Type\Type $key_type_spec;

    /**
     * @psalm-var Type\Type<Tv>
     */
    private Type\Type $value_type_spec;

    /**
     * @psalm-param Type\Type<Tk> $key_type_spec
     * @psalm-param Type\Type<Tv> $value_type_spec
     */
    public function __construct(
        Type\Type $key_type_spec,
        Type\Type $value_type_spec
    ) {
        $this->key_type_spec = $key_type_spec;
        $this->value_type_spec = $value_type_spec;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return iterable<Tk, Tv>
     *
     * @throws TypeCoercionException
     */
    public function coerce($value): iterable
    {
        if (Type\is_iterable($value)) {
            $key_trace = $this->getTrace()->withFrame(Str\format('iterable<%s, _>', $this->key_type_spec->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('iterable<_, %s>', $this->value_type_spec->toString()));

            /** @psalm-var Type\Type<Tk> $key_type_spec */
            $key_type_spec = $this->key_type_spec->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type_speec */
            $value_type_spec = $this->value_type_spec->withTrace($value_trace);

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
                $k = $key_type_spec->coerce($k);
                /** @psalm-var Tv $v */
                $v = $value_type_spec->coerce($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            return $iterator;
        }

        throw TypeCoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return iterable<Tk, Tv>
     *
     * @psalm-assert iterable<Tk, Tv> $value
     *
     * @throws TypeAssertException
     */
    public function assert($value): iterable
    {
        if (Type\is_iterable($value)) {
            $key_trace = $this->getTrace()->withFrame(Str\format('iterable<%s, _>', $this->key_type_spec->toString()));
            $value_trace = $this->getTrace()->withFrame(Str\format('iterable<_, %s>', $this->value_type_spec->toString()));

            /** @psalm-var Type\Type<Tk> $key_type_spec */
            $key_type_spec = $this->key_type_spec->withTrace($key_trace);
            /** @psalm-var Type\Type<Tv> $value_type_spec */
            $value_type_spec = $this->value_type_spec->withTrace($value_trace);

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
                $k = $key_type_spec->assert($k);
                /** @psalm-var Tv $v */
                $v = $value_type_spec->assert($v);
                $entries[] = [$k, $v];
            }

            /** @psalm-var Iter\Iterator<Tk, Tv> $iterator */
            $iterator = Iter\from_entries($entries);

            return $iterator;
        }

        throw TypeAssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format('iterable<%s, %s>', $this->key_type_spec->toString(), $this->value_type_spec->toString());
    }
}
