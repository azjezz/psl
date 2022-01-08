<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Collection;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_iterable;
use function is_object;

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
     * @param Type\TypeInterface<T> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $value_type
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return Collection\MutableVectorInterface<T>
     */
    public function coerce(mixed $value): Collection\MutableVectorInterface
    {
        if (is_iterable($value)) {
            $value_trace = $this->getTrace()->withFrame(
                Str\format('%s<%s>', Collection\MutableVectorInterface::class, $this->value_type->toString())
            );

            /** @var Type\Type<T> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @var list<T> $values
             */
            $values = [];

            /**
             * @var T $v
             */
            foreach ($value as $v) {
                $values[] = $value_type->coerce($v);
            }

            /** @var Collection\MutableVector<T> */
            return new Collection\MutableVector($values);
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @throws AssertException
     *
     * @return Collection\MutableVectorInterface<T>
     *
     * @psalm-assert Collection\MutableVectorInterface<T> $value
     */
    public function assert(mixed $value): Collection\MutableVectorInterface
    {
        if (is_object($value) && $value instanceof Collection\MutableVectorInterface) {
            $value_trace = $this->getTrace()->withFrame(
                Str\format('%s<%s>', Collection\MutableVectorInterface::class, $this->value_type->toString())
            );

            /** @var Type\Type<T> $value_type */
            $value_type = $this->value_type->withTrace($value_trace);

            /**
             * @var list<T> $values
             */
            $values = [];

            /**
             * @var T $v
             */
            foreach ($value as $v) {
                $values[] = $value_type->coerce($v);
            }

            /** @var Collection\MutableVector<T> */
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
