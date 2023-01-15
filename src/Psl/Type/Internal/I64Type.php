<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;

/**
 * @ara-extends Type\Type<i64>
 *
 * @extends Type\Type<int>
 *
 * @internal
 */
final class I64Type extends Type\Type
{
    /**
     * @ara-assert-if-true i64 $value
     *
     * @psalm-assert-if-true int $value
     */
    public function matches(mixed $value): bool
    {
        return is_int($value);
    }

    /**
     * @throws CoercionException
     *
     * @ara-return i64
     *
     * @return int
     */
    public function coerce(mixed $value): int
    {
        return Type\int()
            ->withTrace($this->getTrace()->withFrame($this->toString()))
            ->coerce($value);
    }

    /**
     * @ara-assert i64 $value
     *
     * @psalm-assert int $value
     *
     * @throws AssertException
     *
     * @ara-return i64
     *
     * @return int
     */
    public function assert(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return 'i64';
    }
}
