<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Override;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;

use function is_int;

/**
 * @extends Type\Type<int<0, 65535>>
 *
 * @internal
 */
final readonly class U16Type extends Type\Type
{
    /**
     * @psalm-assert-if-true int<0, 65535> $value
     */
    #[Override]
    public function matches(mixed $value): bool
    {
        return is_int($value) && $value >= 0 && $value <= 65_535;
    }

    /**
     * @throws CoercionException
     *
     * @return int<0, 65535>
     */
    #[Override]
    public function coerce(mixed $value): int
    {
        $integer = Type\int()->coerce($value);

        if ($integer >= 0 && $integer <= 65_535) {
            return $integer;
        }

        throw CoercionException::withValue($value, $this->toString());
    }

    /**
     * @psalm-assert int<0, 65535> $value
     *
     * @throws AssertException
     *
     * @return int<0, 65535>
     */
    #[Override]
    public function assert(mixed $value): int
    {
        if (is_int($value) && $value >= 0 && $value <= 65_535) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString());
    }

    #[Override]
    public function toString(): string
    {
        return 'u16';
    }
}
