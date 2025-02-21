<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Str;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Throwable;

use function is_array;
use function is_iterable;

/**
 * @template Tv
 *
 * @extends Type\Type<non-empty-list<Tv>>
 *
 * @internal
 */
final readonly class NonEmptyVecType extends Type\Type
{
    /**
     * @psalm-mutation-free
     *
     * @param Type\TypeInterface<Tv> $value_type
     */
    public function __construct(
        private readonly Type\TypeInterface $value_type,
    ) {
    }

    /**
     * @psalm-assert-if-true non-empty-list<Tv> $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if ([] === $value) {
            return false;
        }

        $index = 0;
        foreach ($value as $k => $v) {
            if ($index !== $k) {
                return false;
            }

            if (!$this->value_type->matches($v)) {
                return false;
            }

            $index++;
        }

        return true;
    }

    /**
     * @throws CoercionException
     *
     * @return non-empty-list<Tv>
     */
    #[\Override]
    public function coerce(mixed $value): iterable
    {
        if (is_iterable($value)) {
            /** @var Type\Type<Tv> $value_type */
            $value_type = $this->value_type;

            /**
             * @var list<Tv> $entries
             */
            $result = [];

            $i = null;
            $v = null;
            $iterating = true;

            try {
                /**
                 * @var Tv $v
                 * @var array-key $i
                 */
                foreach ($value as $i => $v) {
                    $iterating = false;
                    $result[] = $value_type->coerce($v);
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

            if ($result === []) {
                throw CoercionException::withValue($value, $this->toString());
            }

            return $result;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @throws AssertException
     *
     * @return non-empty-list<Tv>
     *
     * @psalm-assert non-empty-list<Tv> $value
     */
    #[\Override]
    public function assert(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw AssertException::withValue($value, $this->toString());
        }

        /** @var Type\Type<Tv> $value_type */
        $value_type = $this->value_type;

        $result = [];

        $i = null;
        $v = null;

        try {
            /**
             * @var Tv $v
             * @var array-key $i
             */
            foreach ($value as $i => $v) {
                $result[] = $value_type->assert($v);
            }
        } catch (AssertException $e) {
            throw AssertException::withValue($v, $this->toString(), PathExpression::path($i), $e);
        }

        if ($result === []) {
            throw AssertException::withValue($value, $this->toString());
        }

        return $result;
    }

    #[\Override]
    public function toString(): string
    {
        return Str\format('non-empty-vec<%s>', $this->value_type->toString());
    }
}
