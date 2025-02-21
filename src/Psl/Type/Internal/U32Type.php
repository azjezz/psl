<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Math;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;
use function Psl\Type;

/**
 * @ara-extends Type\Type<u32>
 *
 * @extends Type\Type<int<0, 4294967295>>
 *
 * @internal
 */
final readonly class U32Type extends Type\Type
{
    /**
     * @ara-assert-if-true u32 $value
     *
     * @psalm-assert-if-true int<0, 4294967295> $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0 && $value <= MATH\UINT32_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @ara-return u32
     *
     * @return int<0, 4294967295>
     */
    #[\Override]
    public function coerce(mixed $value): int
    {
        $integer = Type\int()->coerce($value);

        if ($integer >= 0 && $integer <= MATH\UINT32_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @ara-assert u32 $value
     *
     * @psalm-assert int<0, 4294967295> $value
     *
     * @throws AssertException
     *
     * @ara-return u32
     *
     * @return int<0, 4294967295>
     */
    #[\Override]
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0 && $value <= MATH\UINT32_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'u32';
    }
}
