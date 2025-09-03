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
 * @extends Type\Type<int<0, 255>>
 *
 * @internal
 */
final readonly class U8Type extends Type\Type
{
    /**
     * @psalm-assert-if-true int<0, 255> $value
     */
    #[\Override]
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0 && $value <= MATH\UINT8_MAX;
    }

    /**
     * @throws CoercionException
     *
     * @return int<0, 255>
     */
    #[\Override]
    public function coerce(mixed $value): int
    {
        $integer = Type\int()->coerce($value);

        if ($integer >= 0 && $integer <= MATH\UINT8_MAX) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert int<0, 255> $value
     *
     * @throws AssertException
     *
     * @return int<0, 255>
     */
    #[\Override]
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0 && $value <= MATH\UINT8_MAX) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[\Override]
    public function toString(): string
    {
        return 'u8';
    }
}
