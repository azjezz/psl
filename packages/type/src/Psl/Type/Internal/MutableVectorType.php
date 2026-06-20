<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Collection;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_iterable;
use function is_object;
use function sprintf;

/**
 * @internal
 */
final readonly class MutableVectorType<T> extends Type\Type<Collection\MutableVectorInterface<T>>
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private Type\TypeInterface<T> $valueType,
    ) {}

    /**
     * @psalm-assert-if-true Collection\MutableVectorInterface<T> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_object($value) || !$value instanceof Collection\MutableVectorInterface) {
            return false;
        }

        // @mago-expect analysis:mixed-assignment
        foreach ($value as $v) {
            if (!$this->valueType->matches($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws CoercionException
     */
    #[Override]
    public function coerce(mixed $value): Collection\MutableVectorInterface<T>
    {
        if (is_iterable($value)) {
            /** @var Type\Type<T> $valueType */
            $valueType = $this->valueType;

            /**
             * @var list<T> $values
             */
            $values = [];
            $i = null;
            $v = null;
            /** @var bool $iterating */
            $iterating = true;

            try {
                /**
                 * @var T $v
                 * @var array-key $i
                 */
                foreach ($value as $i => $v) {
                    $iterating = false;
                    $values[] = $valueType->coerce($v);
                    $iterating = true;
                }
            } catch (Throwable $e) {
                throw match (true) {
                    $iterating => CoercionException::withValue(
                        null,
                        $this->toString(),
                        PathExpression::iteratorError($i),
                        $e,
                    ),
                    default => CoercionException::withValue($v, $this->toString(), PathExpression::path($i), $e),
                };
            }

            return new Collection\MutableVector::<T>($values);
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @psalm-assert Collection\MutableVectorInterface<T> $value
     */
    #[Override]
    public function assert(mixed $value): Collection\MutableVectorInterface<T>
    {
        if (is_object($value) && $value instanceof Collection\MutableVectorInterface) {
            /** @var Type\Type<T> $valueType */
            $valueType = $this->valueType;

            /**
             * @var list<T> $values
             */
            $values = [];
            $i = null;
            $v = null;

            try {
                /**
                 * @var array-key $i
                 * @var T $v
                 */
                foreach ($value as $i => $v) {
                    $values[] = $valueType->assert($v);
                }
            } catch (AssertException $e) {
                throw AssertException::withValue($v, $this->toString(), PathExpression::path($i), $e);
            }

            return new Collection\MutableVector::<T>($values);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return sprintf('%s<%s>', Collection\MutableVectorInterface::class, $this->valueType->toString());
    }
}
