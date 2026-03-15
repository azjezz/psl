<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function array_is_list;
use function is_array;
use function is_iterable;

/**
 * @template Tv
 *
 * @extends Type\Type<list<Tv>>
 *
 * @internal
 *
 * @mago-expect analysis:mixed-assignment
 */
final readonly class VecType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tv> $valueType
     */
    public function __construct(
        private readonly Type\TypeInterface $valueType,
    ) {}

    /**
     * @psalm-assert-if-true list<Tv> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }

        foreach ($value as $v) {
            if ($this->valueType->matches($v)) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @throws CoercionException
     *
     * @return list<Tv>
     */
    #[Override]
    public function coerce(mixed $value): iterable
    {
        if (!is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString());
        }

        /**
         * @var list<Tv> $entries
         */
        $result = [];
        $valueType = $this->valueType;
        $i = null;
        $v = null;
        /** @var bool $iterating */
        $iterating = true;

        try {
            /**
             * @var Tv $v
             * @var array-key $i
             */
            foreach ($value as $i => $v) {
                $iterating = false;
                $result[] = $valueType->coerce($v);
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

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return list<Tv>
     *
     * @psalm-assert list<Tv> $value
     */
    #[Override]
    public function assert(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        $result = [];
        $valueType = $this->valueType;
        $i = null;
        $v = null;

        try {
            /** @var Tv $v */
            foreach ($value as $i => $v) {
                $result[] = $valueType->assert($v);
            }
        } catch (AssertException $e) {
            throw AssertException::withValue($v, $this->toString(), PathExpression::path($i), $e);
        }

        return $result;
    }

    #[Override]
    public function toString(): string
    {
        return 'vec<' . $this->valueType->toString() . '>';
    }
}
