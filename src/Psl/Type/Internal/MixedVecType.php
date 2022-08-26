<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

/**
 * @extends Type\Type<list<mixed>>
 *
 * @internal
 */
final class MixedVecType extends Type\Type
{
    /**
     * @psalm-assert-if-true list<Tv> $value
     */
    public function matches(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    /**
     * @throws CoercionException
     *
     * @return list<mixed>
     */
    public function coerce(mixed $value): iterable
    {
        if (! is_iterable($value)) {
            throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
        }

        if (is_array($value)) {
            if (! array_is_list($value)) {
                return array_values($value);
            }

            return $value;
        }

        /**
         * @var list<mixed> $entries
         */
        $result = [];

        /**
         * @var mixed $v
         *
         * @psalm-suppress MixedAssignment
         */
        foreach ($value as $v) {
            $result[] = $v;
        }

        return $result;
    }

    /**
     * @throws AssertException
     *
     * @return list<mixed>
     *
     * @psalm-assert list<mixed> $value
     */
    public function assert(mixed $value): array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            throw AssertException::withValue($value, $this->toString(), $this->getTrace());
        }

        return $value;
    }

    public function toString(): string
    {
        return 'vec<mixed>';
    }
}
