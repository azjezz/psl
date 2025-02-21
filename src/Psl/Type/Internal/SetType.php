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
 * @template T of array-key
 *
 * @extends Type\Type<Collection\SetInterface<T>>
 *
 * @internal
 */
final readonly class SetType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<T> $type
     */
    public function __construct(
        private readonly Type\TypeInterface $type,
    ) {
    }

    /**
     * @throws CoercionException
     *
     * @return Collection\SetInterface<T>
     */
    #[\Override]
    public function coerce(mixed $value): Collection\SetInterface
    {
        if (is_iterable($value)) {
            /** @var Type\Type<T> $type */
            $type = $this->type;
            /** @var array<T, T> $set */
            $set = [];
            $k = null;
            $v = null;
            $iterating = true;
            try {
                /**
                 * @var array-key $k
                 * @var T $v
                 */
                foreach ($value as $k => $v) {
                    $iterating = false;
                    $v = $type->coerce($v);
                    $set[$v] = $v;
                    $iterating = true;
                }
            } catch (Throwable $e) {
                if ($iterating) {
                    throw CoercionException::withValue(null, $this->toString(), PathExpression::iteratorError($k), $e);
                }

                throw CoercionException::withValue($v, $this->toString(), PathExpression::path($k), $e);
            }

            /** @var Collection\Set<T> */
            return new Collection\Set($set);
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return Collection\SetInterface<T>
     *
     * @psalm-assert Collection\SetInterface<T> $value
     */
    #[\Override]
    public function assert(mixed $value): Collection\SetInterface
    {
        if (is_object($value) && $value instanceof Collection\SetInterface) {
            /** @var Type\Type<T> $type */
            $type = $this->type;
            /** @var array<T, T> $set */
            $set = [];
            $v = null;
            $k = null;
            $iterating = true;
            try {
                /**
                 * @var T $v
                 */
                foreach ($value as $k => $v) {
                    $iterating = false;
                    $v = $type->assert($v);
                    $set[$v] = $v;
                    $iterating = true;
                }
            } catch (Throwable $e) {
                if ($iterating) {
                    throw AssertException::withValue(null, $this->toString(), PathExpression::iteratorError($k), $e);
                }

                throw AssertException::withValue($v, $this->toString(), PathExpression::path($k), $e);
            }

            /** @var Collection\Set<T> */
            return new Collection\Set($set);
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return Str\format('%s<%s>', Collection\SetInterface::class, $this->type->toString());
    }
}
