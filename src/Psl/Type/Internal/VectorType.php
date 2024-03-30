<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Collection;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_iterable;
use function is_object;

/**
 * @template T
 *
 * @extends Type\Type<Collection\VectorInterface<T>>
 *
 * @internal
 */
final class VectorType extends Type\Type
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
     * @return Collection\VectorInterface<T>
     */
    public function coerce(mixed $value): Collection\VectorInterface
    {
        if (is_iterable($value)) {
            /** @var Type\Type<T> $value_type */
            $value_type = $this->value_type;

            /**
             * @var list<T> $values
             */
            $values = [];
            $i = $v = null;
            $iterating = true;

            try {
                /**
                 * @var T $v
                 * @var array-key $i
                 */
                foreach ($value as $i => $v) {
                    $iterating = false;
                    $values[] = $value_type->coerce($v);
                    $iterating = true;
                }
            } catch (Throwable $e) {
                throw match (true) {
                    $iterating => CoercionException::withValue(null, $this->toString(), PathExpression::iteratorError($i), $e),
                    default => CoercionException::withValue($v, $this->toString(), PathExpression::path($i), $e)
                };
            }

            /** @var Collection\Vector<T> */
            return new Collection\Vector($values);
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @param mixed $value
     *
     * @throws AssertException
     *
     * @return Collection\VectorInterface<T>
     *
     * @psalm-assert Collection\VectorInterface<T> $value
     */
    public function assert(mixed $value): Collection\VectorInterface
    {
        if (is_object($value) && $value instanceof Collection\VectorInterface) {
            /** @var Type\Type<T> $value_type */
            $value_type = $this->value_type;

            /**
             * @var list<T> $values
             */
            $values = [];
            $i = $v = null;

            try {
                /**
                 * @var T $v
                 * @var array-key $i
                 */
                foreach ($value as $i => $v) {
                    $values[] = $value_type->assert($v);
                }
            } catch (AssertException $e) {
                throw AssertException::withValue($v, $this->toString(), PathExpression::path($i), $e);
            }

            /** @var Collection\Vector<T> */
            return new Collection\Vector($values);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    public function toString(): string
    {
        return Str\format(
            '%s<%s>',
            Collection\VectorInterface::class,
            $this->value_type->toString(),
        );
    }
}
