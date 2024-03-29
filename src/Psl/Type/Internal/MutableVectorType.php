<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Collection;
use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Exception\PathExpression;
use Throwable;

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

            /** @var Collection\MutableVector<T> */
            return new Collection\MutableVector($values);
        }

        throw CoercionException::withValue($value, $this->toString());
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

            /** @var Collection\MutableVector<T> */
            return new Collection\MutableVector($values);
        }

        throw AssertException::withValue($value, $this->toString());
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
