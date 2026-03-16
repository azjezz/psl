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
 * @template T
 *
 * @extends Type\Type<Collection\VectorInterface<T>>
 *
 * @internal
 */
final readonly class VectorType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<T> $valueType
     */
    public function __construct(
        private Type\TypeInterface $valueType,
    ) {}

    /**
     * @psalm-assert-if-true Collection\VectorInterface<T> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_object($value) || !$value instanceof Collection\VectorInterface) {
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
     *
     * @return Collection\VectorInterface<T>
     */
    #[Override]
    public function coerce(mixed $value): Collection\VectorInterface
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
    #[Override]
    public function assert(mixed $value): Collection\VectorInterface
    {
        if (is_object($value) && $value instanceof Collection\VectorInterface) {
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
                 * @var T $v
                 * @var array-key $i
                 */
                foreach ($value as $i => $v) {
                    $values[] = $valueType->assert($v);
                }
            } catch (AssertException $e) {
                throw AssertException::withValue($v, $this->toString(), PathExpression::path($i), $e);
            }

            return new Collection\Vector($values);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return sprintf('%s<%s>', Collection\VectorInterface::class, $this->valueType->toString());
    }
}
