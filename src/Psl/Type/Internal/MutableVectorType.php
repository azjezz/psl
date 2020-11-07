<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Collection;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @template T
 *
 * @extends Type\Type<Collection\MutableVectorInterface<T>>
 *
 * @internal
 */
final class MutableVectorType extends Type\Type
{
    /**
     * @psalm-var Type\Type<T>
     */
    private Type\Type $value_type;

    /**
     * @psalm-param Type\Type<T> $value_type
     */
    public function __construct(
        Type\Type $value_type
    ) {
        $this->value_type = $value_type;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Collection\MutableVectorInterface<T>
     *
     * @throws CoercionException
     */
    public function coerce($value): Collection\MutableVectorInterface
    {
        if (Type\is_iterable($value)) {
            $value_trace = $this->getTrace()->withFrame(
                Str\format('%s<%s>', Collection\MutableVectorInterface::class, $this->value_type->toString())
            );

            /** @psalm-var Type\Type<T> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<T> $values
             */
            $values = [];

            /**
             * @psalm-var mixed $v
             */
            foreach ($value as $v) {
                /** @psalm-var T $v */
                $v = $value_type->coerce($v);

                $values[] = $v;
            }

            return new Collection\MutableVector($values);
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return Collection\MutableVectorInterface<T>
     *
     * @psalm-assert Collection\MutableVectorInterface<T> $value
     *
     * @throws AssertException
     */
    public function assert($value): Collection\MutableVectorInterface
    {
        if (Type\is_object($value) && Type\is_instanceof($value, Collection\MutableVectorInterface::class)) {
            $value_trace = $this->getTrace()->withFrame(
                Str\format('%s<%s>', Collection\MutableVectorInterface::class, $this->value_type->toString())
            );

            /** @psalm-var Type\Type<T> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @psalm-var list<T> $values
             */
            $values = [];

            /**
             * @psalm-var mixed $v
             */
            foreach ($value as $v) {
                /** @psalm-var T $v */
                $v = $value_type->coerce($v);

                $values[] = $v;
            }

            return new Collection\MutableVector($values);
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return Str\format(
            '%s<%s>',
            Collection\MutableVectorInterface::class,
            $this->value_type->toString(),
        );
    }
}
